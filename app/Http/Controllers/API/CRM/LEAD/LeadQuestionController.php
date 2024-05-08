<?php

namespace App\Http\Controllers\API\CRM\LEAD;

use App\Models\Lead;
use App\Models\User;
use App\Models\LeadQuestion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LeadQuestionOptions;
use App\Models\LeadQuestionAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use App\Models\ChannelPartner;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\CRM\LeadQuotationController;
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Quotation\QuotationMasterController;

// use Illuminate\Http\Request;

class LeadQuestionController extends Controller
{

    public function searchQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
			'status' => ['required'],
			'lead_id' => ['required'],
		]);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            $selectColumn = array(
            'lead_question.id',
            'lead_question.status',
            'lead_question.tag',
            'lead_question.type',
            'lead_question.question',
            'lead_question.is_static',
            'lead_question.is_required',
            'lead_question.sequence',
            'lead_question.is_depend_on_answer',
            'lead_question.depended_question_id',
            'lead_question.depended_question_answer',
            'lead_question.is_active'
            );
            $question = LeadQuestion::select($selectColumn)->where('status', $request->status)->where('tag', 2)->where('is_active', 1)->get();
            $question_list = LeadQuestion::select($selectColumn)->addSelect(DB::raw("'' as value"))->where('status', $request->status)->where('tag', 2)->where('is_active', 1)->get();


            $finalQuestion = [];
            foreach ($question as $key => $value) {
                $checkForVisible = 0;
                $option_temp = LeadQuestionOptions::query()->where('lead_question_id', $value->id)->get();
                $question[$key]['options'] = $option_temp;

                if ($value->is_depend_on_answer == 1) {
                    $dependedQuestion = LeadQuestion::find($value->depended_question_id);

                    if ($dependedQuestion && $dependedQuestion->status != $request->status) {
                        $dependedAnswer = LeadQuestionAnswer::where('lead_id', $request->lead_id)->where('lead_question_id', $dependedQuestion->id)->first();
                        if ($dependedAnswer && $dependedAnswer->answer == $value->depended_question_answer) {
                            if ($dependedQuestion->type == 6 || $dependedQuestion->type == 4) {
                                $dependedAnswer = explode(',', $dependedAnswer->answer);
                                if (in_array($value->depended_question_answer, $dependedAnswer)) {
                                    $checkForVisible = 1;
                                }
                            } elseif ($dependedAnswer->answer == $value->depended_question_answer) {
                                $checkForVisible = 1;
                            }
                        }
                    } else {
                        $checkForVisible = 1;
                    }
                } else {
                    $checkForVisible = 1;
                }

                if ($value->type == 1 || $value->type == 4 || $value->type == 6) {
                    $TypeOption = LeadQuestionOptions::select('id', 'option', 'is_database_side')->where('lead_question_id', $value->id)->orderBy('id', 'asc')->get();
                    $question[$key]['options'] = $TypeOption;
                    foreach ($TypeOption as $Opkey => $Opvalue) {
                        if ($Opvalue['is_database_side'] == 1) {
                            $LeadOwner = Lead::find($request->lead_id)['assigned_to'];
                            // $LeadOwnerCity = User::find($LeadOwner)['city_id'];
                            $LeadOwnerCity = SalesCity($LeadOwner);

                            $ChannelPartner_list = User::select('users.id', 'channel_partner.firm_name as text', 'channel_partner.firm_name as option');
                            $ChannelPartner_list->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
                            $ChannelPartner_list->whereIn('users.city_id', $LeadOwnerCity);
                            $ChannelPartner_list->whereIn('users.type', [101, 102, 103, 104, 105]);
                            $ChannelPartner_list = $ChannelPartner_list->get();

                            $question[$key]['user_list'] = $ChannelPartner_list;
                        }
                    }
                }

                if ($checkForVisible == 1) {
                    $cFinalQuestion = count($finalQuestion);
                    $finalQuestion[$cFinalQuestion] = $question[$key];
                    if ($question[$key]->is_depend_on_answer == 1) {
                        $dependedQuestion = LeadQuestion::find($question[$key]['depended_question_id']);
                        if ($dependedQuestion) {
                            $question[$key]['depended_question'] = $dependedQuestion;
                        } else {
                            $question[$key]->is_depend_on_answer = 0;
                        }
                    }
                }
            }

            $response = successRes('Successfully get Lead Questions');
			$response['data'] = $question;
			$response['question_list'] = $question_list;
			$response['checkForVisible'] = $checkForVisible;
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function saveLeadStatusAnswer(Request $request)
    {
        $rules = [];
        $rules['data'] = 'required';
        $customMessage = [];
        $validator = Validator::make($request->all(), $rules, $customMessage);
        if ($validator->fails()) {
            $response = errorRes();
            $response['msg'] = $validator->errors()->first();
            $response['data'] = $validator->errors();
            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $bodyData = $request->all()['data'];
            $Lead = Lead::find($request->lead_id);
            if ($Lead) {
                if (count($bodyData) > 0) {
                    
                    $AnswerIds = [];
                    foreach ($bodyData as $key => $value) {
                        $answer = '';
                        if(isset($value['value'])){
                            $leadQuestionAnswer = new LeadQuestionAnswer();
                            $leadQuestionAnswer->lead_question_id = $value['id'];
                            $leadQuestionAnswer->question_type = $value['type'];
                            $leadQuestionAnswer->lead_id = $request->lead_id;
                            $leadQuestionAnswer->reference_type = 'Lead-Status-Update';
                            $leadQuestionAnswer->reference_id = $request->lead_id;
                            $leadQuestionAnswer->answer = $value['value'];
                            $leadQuestionAnswer->save();
    
                            if ($leadQuestionAnswer) {
                                array_push($AnswerIds, $leadQuestionAnswer->id);
                            }
                        }
                    }

                    $response = successRes();
                    $response['data'] = $AnswerIds;
                    $response['data']['lead_id'] = $request->lead_id;
                }else {
                    $response = errorRes('question answer not proper');
                }
            }else {
                $response = errorRes('Lead not valid');
            }
            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }
}
