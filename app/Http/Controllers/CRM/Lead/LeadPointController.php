<?php

namespace App\Http\Controllers\CRM\Lead;

use App\Http\Controllers\Controller;

use App\Models\CRMSettingFileTag;
use App\Models\LeadFile;
use App\Models\LeadContact;
use App\Models\Lead;
use App\Models\Architect;
use App\Models\Electrician;
use App\Models\LeadQuestion;
use App\Models\LeadQuestionOptions;
use App\Models\LeadQuestionAnswer;
use App\Models\LeadTask;
use App\Models\User;
use App\Models\CRMHelpDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use DB;

class LeadPointController extends Controller
{

    public function __construct()
    {

        $this->middleware(function ($request, $next) {

            $tabCanAccessBy = array(0, 1, 2, 6,9, 11, 13, 101, 102, 103, 104, 105, 202, 302,13);

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }

            return $next($request);
        });
    }

    function pointAjax(Request $request)
    {
        $Query = LeadFile::query();
        $Query->select('crm_setting_file_tag.name as tag_name', 'lead_files.*', 'users.first_name', 'users.last_name');
        $Query->leftJoin('crm_setting_file_tag', 'crm_setting_file_tag.id', '=', 'lead_files.file_tag_id');
        $Query->leftJoin('users', 'users.id', '=', 'lead_files.uploaded_by');
        $Query->where('lead_files.lead_id', $request->lead_id);
        $Query->where('lead_files.file_tag_id', 3);
        $Query->where('lead_files.is_active', 1);
        $recordsTotal = $Query->count();
        $recordsFiltered = $recordsTotal;

        $LeadFile = LeadFile::query();
        $LeadFile->select('crm_setting_file_tag.name as tag_name', 'lead_files.*', 'users.first_name', 'users.last_name');
        $LeadFile->leftJoin('crm_setting_file_tag', 'crm_setting_file_tag.id', '=', 'lead_files.file_tag_id');
        $LeadFile->leftJoin('users', 'users.id', '=', 'lead_files.uploaded_by');
        $LeadFile->where('lead_files.lead_id', $request->lead_id);
        $LeadFile->where('lead_files.is_active', 1);
        $LeadFile->where('lead_files.file_tag_id', 3);
        $LeadFile->limit($request->length);
        $LeadFile->offset($request->start);
        $LeadFile->orderBy('lead_files.id', 'desc');
        $LeadFile = $LeadFile->get();
        $LeadFile = json_encode($LeadFile);
        $LeadFile = json_decode($LeadFile, true);

        $viewData = array();
        foreach ($LeadFile as $key => $value) {

            $fileHtml = '';
            foreach (explode(",", $value['name']) as $filekey => $filevalue) {
                $fileHtml .= '<a class="ms-1" target="_blank" href="' . getSpaceFilePath($filevalue) . '"><i class="bx bxs-file-pdf"></i></a>';
            }

            if (isset($request->arc_ele) && $request->arc_ele == 1) {
                $viewData[$key]['bill_attached'] = $fileHtml;
                $action = "";
                if ($value['status'] == 100) {
                    $viewData[$key]['bill_amount'] = '<input type="number" readonly  class="form-control billing_amount_value" placeholder="Enter Bill Amount" id="' . $value['id'] . '" onkeyup="changepoint(' . $value['id'] . ')" value="' . $value['billing_amount'] . '" readonly>';
                    $viewData[$key]['point'] = '<input type="text" readonly class="form-control total_point_value" placeholder="Enter Point" id="point_' . $value['id'] . '" readonly value="' . $value['point'] . '">';
                    $answer = LeadQuestionAnswer::query()->where('reference_id', $value['id'])->where('reference_type', 'Bill-Query')->first();
                    if ($answer) {
                        $option = LeadQuestionOptions::query()->whereIn('id', explode(',', $answer->answer))->get();
                        $query = "";
                        foreach ($option as $option_key => $option_value) {
                            $query .= '<span>' . $option_value->option . '</span><br>';
                        }
                        $viewData[$key]['query'] = '<div class="text-center">' . $query . '</div>';
                    } else {
                        $viewData[$key]['query'] = '<div class="text-center">-</div>';
                    }

                    $viewData[$key]['lapsed'] = '<div class="text-center">-</div>';
                    $action .= '<div class="text-center"><span class="badge badge-soft-success  badge-pill badgefont-size-11">CLAIMED</span></div>';
                    if ($value['hod_approved'] == 1) {
                        $viewData[$key]['hod_approved'] = '<div class="text-center"><span class="badge badge-soft-success  badge-pill badgefont-size-11">HOD APRROVED</span></div>';
                    } else if ($value['hod_approved'] == 2) {
                        $viewData[$key]['hod_approved'] = '<div class="text-center"><span class="badge badge-soft-danger  badge-pill badgefont-size-11">HOD REJECTED</span></div>';
                    } else {
                        if (Auth::user()->type == 0) {
                            $viewData[$key]['hod_approved'] = '<div class="text-center"><select class="form-control select2-ajax" id="is_hod_approved" onchange="HodApproved(' . $value['id'] . ', ' . $request->lead_id . ', ' . $value['point'] . ')"><option value="0">Please select a status</option><option value="1">Approved</option><option value="2">Rejected</option></select></div>';
                        } else {
                            $viewData[$key]['hod_approved'] = '<div class="text-center">-</div>';
                        }
                    }
                } else if ($value['status'] == 101) {
                    $viewData[$key]['bill_amount'] = '<input type="number" readonly class="form-control billing_amount_value" placeholder="Enter Bill Amount" id="' . $value['id'] . '" onkeyup="changepoint(' . $value['id'] . ')" value="' . $value['billing_amount'] . '" >';
                    $viewData[$key]['point'] = '<input type="text" readonly class="form-control total_point_value" placeholder="Enter Point" id="point_' . $value['id'] . '"  value="' . $value['point'] . '" readonly>';
                    $answer = LeadQuestionAnswer::query()->where('reference_id', $value['id'])->where('reference_type', 'Bill-Query')->first();
                    if ($answer) {
                        $option = LeadQuestionOptions::query()->whereIn('id', explode(',', $answer->answer))->get();
                        $query = "";
                        foreach ($option as $option_key => $option_value) {
                            $query .= '<span>' . $option_value->option . '</span><br>';
                        }
                    }
                    $viewData[$key]['query'] = '<div class="text-center">' . $query . '</div>';
                    if (isAdminOrCompanyAdmin() == 1 || isCreUser() == 1) {
                        $viewData[$key]['lapsed'] = '<div class="text-center"><button onclick="PointLapsed(' . $value['id'] . ', 102)" class="btn btn-sm btn-primary" style="" disabled>Lapsed</button></div>';
                        $action .= '<div class="text-center"><button onclick="SaveBillingAmount(' . $value['id'] . ')" class="btn btn-sm btn-primary" style="" disabled id="claimed_btn_' . $value['id'] . '">Claim</button></div>';
                    } else {
                        $viewData[$key]['lapsed'] = '-';
                        $action .= '-';
                    }
                    $viewData[$key]['hod_approved'] = '<div class="text-center">-</div>';
                } else if ($value['status'] == 102) {
                    $viewData[$key]['bill_amount'] = '<input type="number" readonly class="form-control billing_amount_value" placeholder="Enter Bill Amount" id="' . $value['id'] . '" onkeyup="changepoint(' . $value['id'] . ')" value="' . $value['billing_amount'] . '" >';
                    $viewData[$key]['point'] = '<input type="text" readonly class="form-control total_point_value" placeholder="Enter Point" id="point_' . $value['id'] . '"  value="' . $value['point'] . '" readonly>';
                    $query_answer = LeadQuestionAnswer::query()->where('reference_id', $value['id'])->where('reference_type', 'Bill-Query')->first();
                    if ($query_answer) {
                        $option = LeadQuestionOptions::query()->whereIn('id', explode(',', $query_answer->answer))->get();
                        $query = "";
                        foreach ($option as $option_key => $option_value) {
                            $query .= '<span>' . $option_value->option . '</span><br>';
                        }
                        $viewData[$key]['query'] = '<div class="text-center"><span>' . $query . '</span></div>';
                    } else {
                        $viewData[$key]['query'] = '<div class="text-center"><span>-</span></div>';
                    }

                    $answer = LeadQuestionAnswer::query()->where('reference_id', $value['id'])->where('reference_type', 'Bill-Lapsed')->first();
                    if ($answer) {
                        $option = LeadQuestionOptions::query()->whereIn('id', explode(',', $answer->answer))->get();
                        $lapsed = "";
                        foreach ($option as $option_key => $option_value) {
                            $lapsed .= '<span>' . $option_value->option . '</span><br>';
                        }
                    } else {
                        $lapsed = "";
                    }
                    $viewData[$key]['lapsed'] = '<div class="text-center">' . $lapsed . '</div>';
                    $action .= '<div class="text-center"><span class="badge badge-soft-danger  badge-pill badgefont-size-11">LAPSED</span></div>';
                    $viewData[$key]['hod_approved'] = '<div class="text-center">-</div>';
                } else {
                    $viewData[$key]['bill_amount'] = '<input type="number"  class="form-control billing_amount_value" placeholder="Enter Bill Amount" id="' . $value['id'] . '" onkeyup="changepoint(' . $value['id'] . ')" value="' . $value['billing_amount'] . '" readonly>';
                    $viewData[$key]['point'] = '<input type="text" class="form-control total_point_value" placeholder="Enter Point" id="point_' . $value['id'] . '"  value="' . $value['point'] . '" readonly>';
                    if (isAdminOrCompanyAdmin() == 1 || isCreUser() == 1) {
                        $viewData[$key]['query'] = '<div class="text-center"><button onclick="PointQuery(' . $value['id'] . ', 101)" class="btn btn-sm me-3" style="background-color: #32C51A; color: #fff;" disabled>Query</button></div>';
                        $viewData[$key]['lapsed'] = '<div class="text-center"><button onclick="PointLapsed(' . $value['id'] . ', 102)" class="btn btn-sm btn-primary" style="" disabled>Lapsed</button></div>';
                        $action .= '<div class="text-center"><button onclick="SaveBillingAmount(' . $value['id'] . ')" class="btn btn-sm btn-primary" style="" id="claimed_btn_' . $value['id'] . '" disabled>Claim</button></div>';
                    } else {
                        $viewData[$key]['query'] = '-';
                        $viewData[$key]['lapsed'] = '-';
                        $action .= '-';
                    }
                    $viewData[$key]['hod_approved'] = '<div class="text-center">-</div>';
                }
            } else {
                $viewData[$key]['bill_attached'] = $fileHtml;
                $action = "";
                if ($value['status'] == 100) {
                    $viewData[$key]['bill_amount'] = '<input type="number" class="form-control billing_amount_value" placeholder="Enter Bill Amount" id="' . $value['id'] . '" onkeyup="changepoint(' . $value['id'] . ')" value="' . $value['billing_amount'] . '" readonly>';
                    $viewData[$key]['point'] = '<input type="text" class="form-control total_point_value" placeholder="Enter Point" id="point_' . $value['id'] . '" readonly value="' . $value['point'] . '">';
                    $answer = LeadQuestionAnswer::query()->where('reference_id', $value['id'])->where('reference_type', 'Bill-Query')->first();
                    if ($answer) {
                        $option = LeadQuestionOptions::query()->whereIn('id', explode(',', $answer->answer))->get();
                        $query = "";
                        foreach ($option as $option_key => $option_value) {
                            $query .= '<span>' . $option_value->option . '</span><br>';
                        }
                        $viewData[$key]['query'] = '<div class="text-center">' . $query . '</div>';
                    } else {
                        $viewData[$key]['query'] = '<div class="text-center">-</div>';
                    }
                    $viewData[$key]['lapsed'] = '<div class="text-center">-</div>';
                    $action .= '<div class="text-center"><span class="badge badge-soft-success  badge-pill badgefont-size-11">CLAIMED</span></div>';
                    if ($value['hod_approved'] == 1) {
                        $viewData[$key]['hod_approved'] = '<div class="text-center"><span class="badge badge-soft-success  badge-pill badgefont-size-11">HOD APRROVED</span></div>';
                    } else if ($value['hod_approved'] == 2) {
                        $viewData[$key]['hod_approved'] = '<div class="text-center"><span class="badge badge-soft-danger  badge-pill badgefont-size-11">HOD REJECTED</span></div>';
                    } else if ($value['hod_approved'] == 3) {
                        $viewData[$key]['hod_approved'] = '<div class="text-center"><span class="badge badge-soft-warning badge-pill badgefont-size-11" id="hod_status" onclick="HodQueryShow(' . $value['id'] . ', ' . $request->lead_id . ', ' . $value['point'] . ')">HOD QUERY</span></div>';
                    } else {
                        if (Auth::user()->type == 0) {
                            // $viewData[$key]['hod_approved'] = '<div class="text-center"><select class="form-control select2-ajax" id="is_hod_approved" onchange="HodApproved('.$value['id'].', '.$request->lead_id.', '.$value['point'].')"><option value="0">Please select a status</option><option value="1">Approved</option><option value="2">Rejected</option></select></div>';
                            $viewData[$key]['hod_approved'] = '<div class="text-center"><span class="badge badge-soft-primary badge-pill badgefont-size-11" id="qurty_status" onclick="StatusApproved(' . $value['id'] . ', ' . $request->lead_id . ', ' . $value['point'] . ')">HOD PENDING</span></div>';
                        } else {
                            $viewData[$key]['hod_approved'] = '<div class="text-center">-</div>';
                        }
                    }
                } else if ($value['status'] == 101) {
                    $viewData[$key]['bill_amount'] = '<input type="number" class="form-control billing_amount_value" placeholder="Enter Bill Amount" id="' . $value['id'] . '" onkeyup="changepoint(' . $value['id'] . ')" value="' . $value['billing_amount'] . '">';
                    $viewData[$key]['point'] = '<input type="text" class="form-control total_point_value" placeholder="Enter Point" id="point_' . $value['id'] . '"  value="' . $value['point'] . '" readonly>';
                    $answer = LeadQuestionAnswer::query()->where('reference_id', $value['id'])->where('reference_type', 'Bill-Query')->first();
                    if ($answer) {
                        $option = LeadQuestionOptions::query()->whereIn('id', explode(',', $answer->answer))->get();
                        $query = "";
                        foreach ($option as $option_key => $option_value) {
                            $query .= '<span>' . $option_value->option . '</span><br>';
                        }
                        $viewData[$key]['query'] = '<div class="text-center">' . $query . '</div>';
                    } else {
                        $viewData[$key]['query'] = '<div class="text-center">-</div>';
                    }
                    if (isAdminOrCompanyAdmin() == 1 || isCreUser() == 1) {
                        $viewData[$key]['lapsed'] = '<div class="text-center"><button onclick="PointLapsed(' . $value['id'] . ', 102)" class="btn btn-sm btn-primary" style="">Lapsed</button></div>';
                        $action .= '<div class="text-center"><button onclick="SaveBillingAmount(' . $value['id'] . ')" class="btn btn-sm btn-primary" style="" id="claimed_btn_' . $value['id'] . '">Claim</button></div>';
                    } else {
                        $viewData[$key]['lapsed'] = '-';
                        $action .= '-';
                    }
                    $viewData[$key]['hod_approved'] = '<div class="text-center">-</div>';
                } else if ($value['status'] == 102) {
                    $viewData[$key]['bill_amount'] = '<input type="number" class="form-control billing_amount_value" placeholder="Enter Bill Amount" id="' . $value['id'] . '" onkeyup="changepoint(' . $value['id'] . ')" value="' . $value['billing_amount'] . '">';
                    $viewData[$key]['point'] = '<input type="text" class="form-control total_point_value" placeholder="Enter Point" id="point_' . $value['id'] . '"  value="' . $value['point'] . '" readonly>';
                    $query_answer = LeadQuestionAnswer::query()->where('reference_id', $value['id'])->where('reference_type', 'Bill-Query')->first();
                    if ($query_answer) {
                        $option = LeadQuestionOptions::query()->whereIn('id', explode(',', $query_answer->answer))->get();
                        $query = "";
                        foreach ($option as $option_key => $option_value) {
                            $query .= '<span>' . $option_value->option . '</span><br>';
                        }
                        $viewData[$key]['query'] = '<div class="text-center"><span>' . $query . '</span></div>';
                    } else {
                        $viewData[$key]['query'] = '<div class="text-center"><span>-</span></div>';
                    }

                    $answer = LeadQuestionAnswer::query()->where('reference_id', $value['id'])->where('reference_type', 'Bill-Lapsed')->first();
                    if ($answer) {
                        $option = LeadQuestionOptions::query()->whereIn('id', explode(',', $answer->answer))->get();
                        $lapsed = "";
                        foreach ($option as $option_key => $option_value) {
                            $lapsed .= '<span>' . $option_value->option . '</span><br>';
                        }
                    } else {
                        $lapsed = "";
                    }
                    $viewData[$key]['lapsed'] = '<div class="text-center">' . $lapsed . '</div>';
                    $action .= '<div class="text-center"><span class="badge badge-soft-danger  badge-pill badgefont-size-11">LAPSED</span></div>';
                    $viewData[$key]['hod_approved'] = '<div class="text-center">-</div>';
                } else {
                    $viewData[$key]['bill_amount'] = '<input type="number" class="form-control billing_amount_value" placeholder="Enter Bill Amount" id="' . $value['id'] . '" onkeyup="changepoint(' . $value['id'] . ')" value="' . $value['billing_amount'] . '">';
                    $viewData[$key]['point'] = '<input type="text" class="form-control total_point_value" placeholder="Enter Point" id="point_' . $value['id'] . '"  value="' . $value['point'] . '" readonly>';
                    if (isAdminOrCompanyAdmin() == 1 || isCreUser() == 1) {
                        $viewData[$key]['query'] = '<div class="text-center"><button onclick="PointQuery(' . $value['id'] . ', 101)" class="btn btn-sm me-3" style="background-color: #32C51A; color: #fff;">Query</button></div>';
                        $viewData[$key]['lapsed'] = '<div class="text-center"><button onclick="PointLapsed(' . $value['id'] . ', 102)" class="btn btn-sm btn-primary" style="">Lapsed</button></div>';
                        $action .= '<div class="text-center"><button onclick="SaveBillingAmount(' . $value['id'] . ')" class="btn btn-sm btn-primary" style="" id="claimed_btn_' . $value['id'] . '">Claim</button></div>';
                    } else {
                        $viewData[$key]['query'] = '-';
                        $viewData[$key]['lapsed'] = '-';
                        $action .= '-';
                    }
                    $viewData[$key]['hod_approved'] = '<div class="text-center">-</div>';
                }
            }
            $action .= '<input type="hidden" id="hidden_file_id_' . $value['id'] . '" value="' . $value['id'] . '">';
            $action .= '<input type="hidden" id="hidden_file_tag_' . $value['id'] . '" value="' . $value['file_tag_id'] . '">';
            $viewData[$key]['action'] = $action;
        }

        $jsonData = array(
            "draw" => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal),
            // total number of records
            "recordsFiltered" => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $viewData, // total data array
            // "data12" => $query, // total data array
        );

        return $jsonData;
    }

    function saveBillingAmount(Request $request)
    {

        if ($request->billing_amount != '' && $request->billing_amount != 0 && $request->billing_amount != null && $request->point != '' && $request->point != 0 && $request->point != null) {
            $Lead_file = LeadFile::find($request->file_id);
            if ($Lead_file) {
                $Lead_file->billing_amount = $request->billing_amount;
                $Lead_file->point = $request->point;
                $Lead_file->status = 100;
                $Lead_file->claimed_date_time = date('Y-m-d H:i:s');
                $Lead_file->save();

                $is_not_clear_bill = LeadFile::query()->where('reference_id', $Lead_file['reference_id'])->whereIn('status', [101, 0])->count();
                if ($is_not_clear_bill == 0) {
                    $Lead_task = LeadTask::find($Lead_file->reference_id);
                    $Lead_task->is_closed = 1;
                    $Lead_task->closed_date_time = date("Y-m-d H:i:s");
                    $Lead_task->outcome_type = 1;
                    $Lead_task->save();

                    if ($Lead_task) {
                        $is_not_close_task = LeadTask::query()->where('assign_to', 12)->where('lead_id', $Lead_task->lead_id)->where('is_closed', 0)->where('is_autogenerate', 1)->count();
                        if ($is_not_close_task == 0) {
                            $Lead = Lead::find($Lead_task->lead_id);
                            $Lead->companyadmin_verification = 2;
                            $Lead->save();
                        }
                    }
                }
            }

            $Lead = Lead::find($request->lead_id);

            $Lead->total_billing_amount = $Lead->total_billing_amount + $request->billing_amount;
            $Lead->total_point = $Lead->total_point + $request->point;
            $Lead->reward_status = 100;
            $Lead->updated_by = Auth::user()->id;
            $Lead->updateip = $request->ip();
            $Lead->update_source = 'WEB';
            $Lead->save();


            if ($Lead) {
                $timeline = array();
                $timeline['lead_id'] = $Lead->id;
                $timeline['type'] = "file-upload";
                $timeline['reffrance_id'] = $Lead->id;
                $timeline['description'] = "Lead Id " . $Lead->id . " Add " . $Lead->total_billing_amount . " Billing Amount And " . $Lead->total_point . " Total Point";
                $timeline['source'] = "WEB";
                saveLeadTimeline($timeline);
                $response = successRes("Successfully Detail Save");
            } else {
                $response = errorRes("Detail Not Saved", 400);
            }
        } else {
            $response = errorRes("Please Enter Billing Amount", 400);
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function pointQueryQuestion(Request $request)
    {
        $Lead = Lead::find($request->lead_id);
        if ($Lead) {
            if ($Lead->status == 103) {
                $question = LeadQuestion::query()->where('tag', 1)->get();
                foreach ($question as $key => $value) {
                    $option_temp = LeadQuestionOptions::query()->where('lead_question_id', $value->id)->get();
                    $question[$key]['options'] = $option_temp;
                }
            }
        }
        $data = array();
        $data['lead_id'] = $Lead->id;
        $data['lead_status'] = $Lead->status;
        $data['question'] = $question;

        $response = successRes("Successfully get Inquiry Questions");
        $response['view'] = view('crm/lead/answer', compact('data'))->render();
        // $response['data'] = $data;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function saveLeadAnswer(Request $request)
    {
        $Lead = Lead::find($request->lead_id);
        // $Lead = $request;

        if ($Lead) {
            $leadQuestion = LeadQuestion::where('status', $request->lead_status)->where('tag', 1)->get();

            if (count($leadQuestion) > 0) {
                $rules = array();
                $customMessage = array();
                foreach ($leadQuestion as $key => $value) {
                    if ($value->is_required == 1) {
                        $rules['lead_questions_' . $value->id] = 'required';
                        $customMessage['lead_questions_' . $value->id . '.required'] = "Please check Q. " . $value->question;
                    }
                }

                $validator = Validator::make($request->all(), $rules, $customMessage);
                if ($validator->fails()) {
                    $response = errorRes();
                    $response['msg'] = "The request could not be understood by the server due to malformed syntax";
                    $response['data'] = $validator->errors();
                    return response()->json($response)->header('Content-Type', 'application/json');
                } else {
                    $leadQuestionAnswer = array();
                    foreach ($leadQuestion as $key => $value) {

                        $leadQuestionAnswer[$value->id]['lead_question_id'] = $value->id;
                        $leadQuestionAnswer[$value->id]['question_type'] = $value->type;
                        $leadQuestionAnswer[$value->id]['lead_id'] = $request->lead_id;
                        $leadQuestionAnswer[$value->id]['reference_id'] = $request->file_id;

                        $Lead_File_Status = LeadFile::find($request->file_id);
                        if ($request->file_status == 101) {
                            $leadQuestionAnswer[$value->id]['reference_type'] = "Bill-Query";
                        } else if ($request->file_status == 102) {
                            $leadQuestionAnswer[$value->id]['reference_type'] = "Bill-Lapsed";
                        } else {
                            $leadQuestionAnswer[$value->id]['reference_type'] = "HOD-Bill-Query";
                        }

                        if ($value->type == 6) {
                            $answerOfMultiCHeck = isset($request->all()['lead_questions_' . $value->id]) ? $request->all()['lead_questions_' . $value->id] : array();
                            $answerOfMultiCHeck = array_keys($answerOfMultiCHeck);
                            $answerOfMultiCHeck = implode(",", $answerOfMultiCHeck);
                            $leadQuestionAnswer[$value->id]['answer'] = $answerOfMultiCHeck;
                        }
                    }

                    $leadQuestionAnswer = array_values($leadQuestionAnswer);
                    $LeadQuestionAnswer = new LeadQuestionAnswer();

                    $leadQuestionAnswer = $LeadQuestionAnswer->insert($leadQuestionAnswer);

                    if ($leadQuestionAnswer) {
                        $Lead_file = LeadFile::find($request->file_id);
                        if ($Lead_file) {

                            if (isset($request->file_status) && $request->file_status != 103) {
                                $Lead_file->status = $request->file_status;
                                if ($request->file_amount != null || $request->file_amount != 0 && $request->file_amount > 0) {
                                    $Lead_file->billing_amount = $request->file_amount;
                                    $Lead_file->point = $request->file_point;
                                }
                                $Lead_file->save();
                            }

                            if (isset($request->file_status) && $request->file_status == 102) {
                                $is_not_clear_bill = LeadFile::query()->where('reference_id', $Lead_file['reference_id'])->whereIn('status', [101, 0])->count();
                                if ($is_not_clear_bill == 0) {
                                    $Lead_task = LeadTask::find($Lead_file->reference_id);
                                    $Lead_task->is_closed = 1;
                                    $Lead_task->closed_date_time = date("Y-m-d H:i:s");
                                    $Lead_task->outcome_type = 1;
                                    $Lead_task->save();

                                    if ($Lead_task) {
                                        $is_not_close_task = LeadTask::query()->where('assign_to', 12)->where('lead_id', $Lead_task->lead_id)->where('is_closed', 0)->where('is_autogenerate', 1)->count();
                                        if ($is_not_close_task == 0) {
                                            $Lead = Lead::find($Lead_task->lead_id);
                                            $Lead->companyadmin_verification = 2;
                                            $Lead->save();
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $response = successRes();
                    return response()->json($response)->header('Content-Type', 'application/json');
                }
            }
        }
    }

    function hodApproved(Request $request)
    {

        if ($request->is_hod_approve == 0) {
            $response = errorRes("Please Select Valid Status");
        } else if ($request->is_hod_approve == 1) {
            $File = LeadFile::find($request->file_id);
            $File->hod_approved = 1;
            $File->hod_approved_at = date('Y-m-d H:i:s');
            $File->save();

            $data = array();

            $LeadContact = LeadContact::query();
            $LeadContact->select('crm_setting_contact_tag.name as tag_name', 'lead_contacts.*');
            $LeadContact->leftJoin('crm_setting_contact_tag', 'crm_setting_contact_tag.id', '=', 'lead_contacts.contact_tag_id');
            $LeadContact->where('lead_contacts.lead_id', $File->lead_id);
            $LeadContact->where('lead_contacts.status', 1);
            $LeadContact = $LeadContact->get();

            // $data['LeadContact'] = $LeadContact;

            $Lead = Lead::select('leads.assigned_to','leads.first_name', 'leads.last_name')->where('id', $File->lead_id)->first();
            foreach ($LeadContact as $key => $value) {
                if ($value['type'] == 202) {
                    $arc_id = explode('-', $value['type_detail'])[2];
                    $Architect = Architect::query()->where('user_id', $arc_id)->first();

                    if ($Architect) {
                        $total_point = $Architect->total_point;
                        $total_current_point = $Architect->total_point_current;
                        $Architect->total_point = $total_point + $File->point;
                        $Architect->total_point_current = $total_current_point + $File->point;

                        $Architect->save();


                        if ($Architect) {

                            $debugLog = array();
                            $debugLog['for_user_id'] = $Architect->user_id;
                            $debugLog['name'] = "point-gain";
                            $debugLog['points'] = $File->point;
                            $debugLog['inquiry_id'] = $File->lead_id;
                            $debugLog['type'] = 'LEAD';
                            $debugLog['description'] = $File->point . " Point gained from deal #" . $File->lead_id . "(" . $Lead->first_name . " " . $Lead->last_name . ")";
                            saveCRMUserLog($debugLog);

                            $User = User::find($Architect->user_id);

                            $query = CRMHelpDocument::query();
                            $query->where('status', 1);
                            $query->where('type', 202);
                            $query->orderBy('publish_date_time', "desc");
                            $helpDocuments = $query->first();

                            $configrationForNotify = configrationForNotify();

                            $getUpperIds = getParentSalePersonsIdsforLead($Lead->assigned_to);
                            $emails = User::select('email')->whereIn('id', $getUpperIds)->distinct()->pluck('email')->all();

                            $Mailarr = [];
                            $Mailarr['from_name'] = $configrationForNotify['from_name'];
                            $Mailarr['from_email'] = $configrationForNotify['from_email'];
                            $Mailarr['to_email'] = $User->email;
                            $Mailarr['to_name'] = $configrationForNotify['to_name'];
                            $Mailarr['bcc_email'] = "sales@whitelion.in, sc@whitelion.in, poonam@whitelion.in,".implode(', ', $emails);
                            $Mailarr['cc_email'] = "";
                            $Mailarr['subject'] = 'You just earned points!';
                            $Mailarr['transaction_id'] = $User->id;
                            $Mailarr['transaction_name'] = "Deal";
                            $Mailarr['transaction_type'] = "Email";
                            $Mailarr['transaction_detail'] = "emails.architect_points";
                            $Mailarr['attachment'] = $helpDocuments->file_name;
                            $Mailarr['lead_id'] = $File->lead_id;
                            $Mailarr['point_value'] = $File->point;
                            $Mailarr['remark'] = "Architect Point Earned";
                            $Mailarr['source'] = "Web";
                            $Mailarr['entryip'] = $request->ip();
                            
                            if (Config::get('app.env') == 'local') {
                                $Mailarr['to_email'] = $configrationForNotify['test_email'];
                                $Mailarr['bcc_email'] = implode(', ', $configrationForNotify['test_email_bcc']);
                            }

                            saveNotificationScheduler($Mailarr);

                            
                        }
                    }
                } else if ($value['type'] == 302) {
                    $ele_id = explode('-', $value['type_detail'])[2];
                    $Electrician = Electrician::query()->where('user_id', $ele_id)->first();

                    if ($Electrician) {
                        $total_point = $Electrician->total_point;
                        $total_current_point = $Electrician->total_point_current;

                        $Electrician->total_point = $total_point + $File->point;
                        $Electrician->total_point_current = $total_current_point + $File->point;

                        $Electrician->save();

                        if ($Electrician) {
                            $debugLog = array();
                            $debugLog['for_user_id'] = $Electrician->user_id;
                            $debugLog['name'] = "point-gain";
                            $debugLog['points'] = $File->point;
                            $debugLog['inquiry_id'] = $File->lead_id;
                            $debugLog['type'] = 'LEAD';
                            $debugLog['description'] = $File->point . " Point gained from deal #" . $File->lead_id . "(" . $Lead->first_name . " " . $Lead->last_name . ")";
                            saveCRMUserLog($debugLog);

                            $User = User::find($Electrician->user_id);

                            $configrationForNotify = configrationForNotify();

                            $query = CRMHelpDocument::query();
                            $query->where('status', 1);
                            $query->where('type', 202);
                            $query->orderBy('publish_date_time', "desc");
                            $helpDocuments = $query->first();

                            // $Mailarr = [];
                            // $Mailarr['from_name'] = $configrationForNotify['from_name'];
                            // $Mailarr['from_email'] = $configrationForNotify['from_email'];
                            // $Mailarr['to_email'] = $User->email;
                            // $Mailarr['to_name'] = $configrationForNotify['to_name'];
                            // $Mailarr['bcc_email'] = "sales@whitelion.in, sc@whitelion.in, poonam@whitelion.in";
                            // $Mailarr['cc_email'] = "";
                            // $Mailarr['subject'] = 'You just earned points!';
                            // $Mailarr['transaction_id'] = $User->id;
                            // $Mailarr['transaction_name'] = "Deal";
                            // $Mailarr['transaction_type'] = "Email";
                            // $Mailarr['transaction_detail'] = "emails.architect_points";
                            // $Mailarr['attachment'] = $helpDocuments->file_name;
                            // $Mailarr['lead_id'] = $File->lead_id;
                            // $Mailarr['point_value'] = $File->point;
                            // $Mailarr['remark'] = "Architect Point Earned";
                            // $Mailarr['source'] = "Web";
                            // $Mailarr['entryip'] = $request->ip();
                            
                            // if (Config::get('app.env') == 'local') {
                            //     $Mailarr['to_email'] = $configrationForNotify['test_email'];
                            //     $Mailarr['bcc_email'] = implode(', ', $configrationForNotify['test_email_bcc']);
                            // }

                            // saveNotificationScheduler($Mailarr);

                            
                        }
                    }
                }
            }
            $response = successRes("HOD APPROVED");
            $response['data'] = $data;
        } else if ($request->is_hod_approve == 2) {
            $File = LeadFile::find($request->file_id);
            $File->hod_approved = 2;
            $File->save();
            $response = successRes("HOD REJECTED");
        } else if ($request->is_hod_approve == 3) {
            $File = LeadFile::find($request->file_id);
            $File->hod_approved = 3;
            $File->save();
            $response = successRes("HOD Query");
        }

        // $response = $request;


        return response()->json($response)->header('Content-Type', 'application/json');
    }
    function hodQueryQuestion(Request $request)
    {
        $Lead = Lead::find($request->lead_id);

        if ($Lead) {
            if ($Lead->status == 103) {
                $Answer = LeadQuestionAnswer::select('lead_id', 'answer', 'reference_type', 'reference_id')
                    ->where('lead_id', $Lead->id)
                    ->where('reference_type', 'HOD-Bill-Query') // Add this condition
                    ->where('reference_id', $request->file_id) // Add this condition
                    ->get();

                if ($Answer->count() > 0) {
                    $selectedOptions = explode(',', $Answer[0]['answer']);
                    $question = LeadQuestion::query()->where('tag', 1)->get();
                    foreach ($question as $key => $value) {
                        $option_temp = LeadQuestionOptions::whereIn('id', $selectedOptions)->get();
                        $question[$key]['options'] = $option_temp;
                    }
                } else {
                    $question = [];
                }
            }
        }
        $data = array();
        $data['lead_id'] = $Lead->id;
        $data['lead_status'] = $Lead->status;
        $data['question'] = $question;

        $response = successRes("Successfully get Inquiry Questions");
        $response['view'] = view('crm/lead/hod_answer', compact('data'))->render();
        $response['data'] = $data;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
}
