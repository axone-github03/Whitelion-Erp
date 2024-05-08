<?php

namespace App\Http\Controllers;

use App\Models\InquiryQuestion;
use App\Models\InquiryQuestionOption;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CRMInquiryQuestionController extends Controller {

	public function __construct() {
		$this->middleware(function ($request, $next) {
			$tabCanAccessBy = array(0, 1);
			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}
			return $next($request);
		});
	}

	public function index(Request $request) {
		$data = array();
		$data['title'] = "Inquiry Question";
		$data['status'] = isset($request->status) ? $request->status : 1;
		return view('crm/inquiry/question', compact('data'));
	}

	function ajax(Request $request) {
		//DB::enableQueryLog();

		$searchColumns = array(
			0 => 'inquiry_questions.id',
			1 => 'inquiry_questions.question',
		);

		$sortingColumns = array(
			1 => 'inquiry_questions.sequence',

		);

		$selectColumns = array(
			0 => 'inquiry_questions.id',
			1 => 'inquiry_questions.type',
			2 => 'inquiry_questions.question',
			3 => 'inquiry_questions.is_static',
			4 => 'inquiry_questions.is_required',
			5 => 'inquiry_questions.status',
			6 => 'inquiry_questions.sequence',
			7 => 'inquiry_questions.is_depend_on_answer',
			8 => 'inquiry_questions.depended_question_id',
		);

		$recordsTotal = InquiryQuestion::query();
		if ($request->status != "") {
			$recordsTotal->where('inquiry_questions.status', $request->status);
		}
		$recordsTotal = $recordsTotal->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = InquiryQuestion::query();
		$query->select($selectColumns);
		if ($request->status != "") {
			$query->where('inquiry_questions.status', $request->status);
		}
		$query->limit($request->length);
		$query->offset($request->start);
		$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
		$isFilterApply = 0;
		$search_value = '';
		
		if (isset($request['search']['value'])) {
			$isFilterApply = 1;
			$search_value = $request['search']['value'];
			$query->where(function ($query) use ($search_value, $searchColumns) {

				for ($i = 0; $i < count($searchColumns); $i++) {

					if ($i == 0) {
						$query->where($searchColumns[$i], 'like', "%" . $search_value . "%");
					} else {

						$query->orWhere($searchColumns[$i], 'like', "%" . $search_value . "%");
					}
				}
			});
		}

		$data = $query->get();

		$data = json_decode(json_encode($data), true);

		if ($isFilterApply == 1) {
			$recordsFiltered = count($data);
		}

		$inquiryStatus = getInquiryStatus();
		foreach ($data as $key => $value) {

			$data[$key]['id'] = "<p>" . highlightString($data[$key]['id'],$search_value) . '</p>';
			$data[$key]['sequence'] = highlightString(($data[$key]['sequence'] + 1),$search_value);
			$data[$key]['status'] = "<p>" . highlightString($inquiryStatus[$data[$key]['status']]['name'],$search_value) . '</p>';

			if ($data[$key]['type'] == 0) {
				$data[$key]['type'] = "<p>" . highlightString("Text", $search_value) . "</p>";
			} elseif ($data[$key]['type'] == 1) {
				$data[$key]['type'] = "<p>" . highlightString("Option", $search_value) . "</p>";
			} elseif ($data[$key]['type'] == 2) {
				$data[$key]['type'] = "<p>" . highlightString("File", $search_value) . "</p>";
			} elseif ($data[$key]['type'] == 3) {
				$data[$key]['type'] = "<p>" . highlightString("Checkbox", $search_value) . "</p>";
			} elseif ($data[$key]['type'] == 4) {
				$data[$key]['type'] = "<p>" . highlightString("Multi Option", $search_value) . "</p>";
			} elseif ($data[$key]['type'] == 5) {
				$data[$key]['type'] = "<p>" . highlightString("Number", $search_value) . "</p>";
			} elseif ($data[$key]['type'] == 6) {
				$data[$key]['type'] = "<p>" . highlightString("Multi Checkbox", $search_value) . "</p>";
			} elseif ($data[$key]['type'] == 7) {
				$data[$key]['type'] = "<p>" . highlightString("Multi File", $search_value) . "</p>";
			}
			if ($data[$key]['is_depend_on_answer'] == 0) {
				$data[$key]['is_depend_on_answer'] = highlightString("No",$search_value);

			} else {
				$data[$key]['is_depend_on_answer'] = "Yes #" . highlightString($data[$key]['depended_question_id'],$search_value);

			}

			$data[$key]['question'] = "<p>" . highlightString($data[$key]['question'],$search_value) . '</p>';
			$is_static = $data[$key]['is_static'];
			if ($data[$key]['is_static'] == 1) {
				$data[$key]['is_static'] = '<span class="badge badge-pill badge-soft-danger font-size-11"> Static</span>';
			} else {
				$data[$key]['is_static'] = '<span class="badge badge-pill badge-soft-success font-size-11"> Dyanmic</span>';
			}

			$data[$key]['is_required'] = "<p>" . highlightString((($data[$key]['is_required'] == 1) ? 'Yes' : 'No'),$search_value) . '</p>';

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a data-id="' . $value['id'] . '"  class="sort-handler" href="javascript:void(0)" ><i class="bx bx-sort"></i></a>';
			$uiAction .= '</li>';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
			$uiAction .= '</li>';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="deleteWarning(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Delete" class="' . (($is_static == 1) ? 'isDisabled' : '') . '" ><i class="bx bx-trash-alt"></i></a>';
			$uiAction .= '</li>';

			$uiAction .= '</ul>';
			$data[$key]['action'] = $uiAction;
		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $data, // total data array
			"data1" => InquiryQuestion::query()->get(), // total data array
		);
		return $jsonData;
	}

	public function saveQuestion(Request $request) {

		$rules = array();
		$rules['inquiry_questions_id'] = 'required';
		$rules['inquiry_questions_status'] = 'required';

		$rules['inquiry_questions_question'] = 'required';
		$rules['inquiry_questions_is_required'] = 'required';
		$rules['inquiry_questions_question'] = 'required';

		//condition for dynamic question is not chnage on edit
		if ($request->inquiry_questions_id == 0) {
			//$rules['inquiry_questions_is_static'] = 'required';
			$rules['inquiry_questions_type'] = 'required';
		}
		if (isset($request->inquiry_questions_type) && $request->inquiry_questions_type == 1) {
			$rules['question_option.*'] = 'required';
		}

		if ($request->is_depend_on_answer == 1) {
			$rules['depended_question_id'] = 'required';
			$rules['depended_question_answer'] = 'required';
		}

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {

			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			return redirect()->back()->with("error", "Something went wrong with validation");
		} else {

			$countQuestion = InquiryQuestion::where('status', $request->inquiry_questions_status)->count();

			if ($request->inquiry_questions_id != 0) {
				$InquiryQuestion = InquiryQuestion::find($request->inquiry_questions_id);
			} else {
				$InquiryQuestion = new InquiryQuestion();
				$InquiryQuestion->is_static = 0;
				$InquiryQuestion->type = $request->inquiry_questions_type;
				$InquiryQuestion->sequence = $countQuestion;
			}

			$InquiryQuestion->status = $request->inquiry_questions_status;
			$InquiryQuestion->type = $request->inquiry_questions_type;
			$InquiryQuestion->question = $request->inquiry_questions_question;
			$InquiryQuestion->is_required = $request->inquiry_questions_is_required;
			$InquiryQuestion->is_depend_on_answer = $request->is_depend_on_answer;

			if ($request->is_depend_on_answer == 1) {

				$InquiryQuestion->depended_question_id = $request->depended_question_id;
				$InquiryQuestion->depended_question_answer = $request->depended_question_answer;
			}
			$InquiryQuestion->save();

			if ($InquiryQuestion) {
				if ($request->inquiry_questions_id != 0) {

					if ($InquiryQuestion->type == 1 || $InquiryQuestion->type == 4 || $InquiryQuestion->type == 6) {

						$previousInquiryQuestionOption = InquiryQuestionOption::select('id')->where('inquiry_question_id', $request->inquiry_questions_id)->get();

						$previousQuestionIds = array();
						foreach ($previousInquiryQuestionOption as $keyP => $valueP) {
							$previousQuestionIds[] = $valueP->id;
						}
						$editOptions = $request->edit_option;

						foreach ($previousQuestionIds as $keyP => $valueP) {
							if (isset($editOptions[$valueP]) && $editOptions[$valueP] != "") {

								$InquiryQuestionOption = InquiryQuestionOption::find($valueP);
								if ($InquiryQuestionOption) {
									$InquiryQuestionOption->option = $editOptions[$valueP];
									$InquiryQuestionOption->save();
								}

								//Edit

							} else {
								//Delete
								$InquiryQuestionOption = InquiryQuestionOption::find($valueP);
								if ($InquiryQuestionOption) {

									$InquiryQuestionOption->delete();
								}

							}
						}

						$questionOption = array();
						if (isset($request->question_option)) {
							foreach ($request->question_option as $key => $val) {
								$questionOption[$key]['inquiry_question_id'] = $InquiryQuestion->id;
								$questionOption[$key]['option'] = $val;
								$questionOption[$key]['created_at'] = date("Y-m-d H:i:s", strtotime('now'));
								$questionOption[$key]['updated_at'] = date("Y-m-d H:i:s", strtotime('now'));
							}

						}

						$InquiryQuestionOption = new InquiryQuestionOption();
						$InquiryQuestionOption->insert($questionOption);
						#logic for add,edit and delete options
						// $InquiryQuestionOption = InquiryQuestionOption::where('inquiry_question_id', $request->inquiry_questions_id)->get();
						// if (count($InquiryQuestionOption)) {

						// 	foreach ($InquiryQuestionOption as $InquiryQuestionOptionKey => $InquiryQuestionOptionValue) {
						// 		//check id is exist or not
						// 		if (in_array($InquiryQuestionOptionValue->id, $request->question_option_id)) {
						// 			$key = array_search($InquiryQuestionOptionValue->id, $request->question_option_id);
						// 			#update
						// 			$InquiryQuestionOption = InquiryQuestionOption::where('id', $InquiryQuestionOptionValue->id)->update(['option' => $request->question_option[$key]]);
						// 		} else {
						// 			#delete
						// 			$InquiryQuestionOption = InquiryQuestionOption::where('id', $InquiryQuestionOptionValue->id)->delete();
						// 		}
						// 	}
						// }
						// //add new option
						// if (isset($request->question_option_id) && count($request->question_option_id) > 0) {
						// 	#create a option array
						// 	$question_option = array();
						// 	foreach ($request->question_option_id as $questionOptionIdKey => $questionOptionIdValue) {
						// 		if ($questionOptionIdValue == '') {
						// 			//add new option
						// 			$question_option[$questionOptionIdKey]['inquiry_question_id'] = $InquiryQuestion->id;
						// 			$question_option[$questionOptionIdKey]['option'] = $request->question_option[$questionOptionIdKey];
						// 			$question_option[$questionOptionIdKey]['created_at'] = date("Y-m-d H:i:s", strtotime('now'));
						// 			$question_option[$questionOptionIdKey]['updated_at'] = date("Y-m-d H:i:s", strtotime('now'));
						// 		}
						// 	}
						// 	$InquiryQuestionOption = new InquiryQuestionOption();
						// 	$InquiryQuestionOption->insert($question_option);
						// }
					}
					$response = successRes("Successfully saved Inquiry");

					$debugLog = array();
					$debugLog['name'] = "inquiry-question-edit";
					$debugLog['description'] = "inquiry #" . $InquiryQuestion->id . "(" . $InquiryQuestion->question . ") has been updated ";
					saveDebugLog($debugLog);
				} else {

					if ($request->inquiry_questions_type == 1 || $request->inquiry_questions_type == 4 || $request->inquiry_questions_type == 6) {
						//create a option array
						$questionOption = array();
						foreach ($request->question_option as $key => $val) {
							$questionOption[$key]['inquiry_question_id'] = $InquiryQuestion->id;
							$questionOption[$key]['option'] = $val;
							$questionOption[$key]['created_at'] = date("Y-m-d H:i:s", strtotime('now'));
							$questionOption[$key]['updated_at'] = date("Y-m-d H:i:s", strtotime('now'));
						}
						$InquiryQuestionOption = new InquiryQuestionOption();
						$InquiryQuestionOption->insert($questionOption);
					}
					$response = successRes("Successfully added Inquiry Question");

					$debugLog = array();
					$debugLog['name'] = "inquiry-question-add";
					$debugLog['description'] = "inquiry #" . $InquiryQuestion->id . "(" . $InquiryQuestion->question . ") has been added ";
					saveDebugLog($debugLog);
				}
			}

			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}

	public function detail(Request $request) {
		$InquiryQuestion = InquiryQuestion::select('*')->find($request->id);
		if ($InquiryQuestion) {
			if ($InquiryQuestion->type == 1 || $InquiryQuestion->type == 4 || $InquiryQuestion->type == 6) {
				$InquiryQuestion['inquiry_question_option'] = InquiryQuestionOption::select('*')->where('inquiry_question_id', $InquiryQuestion->id)->get();
			}

			$InquiryQuestion = json_decode(json_encode($InquiryQuestion), true);

			$response = successRes("Successfully get Inquiry Question");
			$response['data'] = $InquiryQuestion;
			if ($InquiryQuestion['is_depend_on_answer'] == 1) {

				$dependedQuestion = InquiryQuestion::select('id', 'question as text', 'type')->find($InquiryQuestion['depended_question_id']);

				if ($dependedQuestion) {

					$response['data']['depended_question'] = array();

					$response['data']['depended_question']['id'] = $dependedQuestion->id;
					$response['data']['depended_question']['text'] = "#" . $dependedQuestion->id . ". " . $dependedQuestion->text;

					$response['data']['has_depended_question_answer'] = 0;

					if ($dependedQuestion->type == 1 || $dependedQuestion->type == 4 || $dependedQuestion->type == 6) {

						$dependedQuestionAnswer = InquiryQuestionOption::select('id', 'option as text')->find($InquiryQuestion['depended_question_answer']);

						if ($dependedQuestionAnswer) {
							$response['data']['has_depended_question_answer'] = 1;
							$response['data']['depended_question_answer'] = array();
							$response['data']['depended_question_answer']['id'] = $dependedQuestionAnswer->id;
							$response['data']['depended_question_answer']['text'] = $dependedQuestionAnswer->text;

						}

					} else if ($dependedQuestion->type == 3) {

						if ($InquiryQuestion['depended_question_answer'] == 1) {

							$response['data']['has_depended_question_answer'] = 1;
							$response['data']['depended_question_answer'] = array();
							$response['data']['depended_question_answer']['id'] = 1;
							$response['data']['depended_question_answer']['text'] = "Checked";

						} else if ($InquiryQuestion['depended_question_answer'] == 0) {

							$response['data']['has_depended_question_answer'] = 1;
							$response['data']['depended_question_answer']['id'] = 0;
							$response['data']['depended_question_answer']['text'] = "Not Checked";

						}

					}

				} else {
					$response['data']['is_depend_on_answer'] = 0;
				}

			}
		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function delete(Request $request) {
		$InquiryQuestion = InquiryQuestion::find($request->id);
		if ($InquiryQuestion && $InquiryQuestion->is_static == 0) {
			$debugLog = array();
			$debugLog['name'] = "inquiry-question-delete";
			$debugLog['description'] = "inquiry question #" . $InquiryQuestion->id . "(" . $InquiryQuestion->question . ") has been deleted";

			saveDebugLog($debugLog);

			$InquiryQuestionOption = InquiryQuestionOption::where('inquiry_question_id', $InquiryQuestion->id)->delete();

			$status = $InquiryQuestion->status;
			$InquiryQuestion->delete();
			$InquiryQuestions = InquiryQuestion::select('id')->where('status', $status)->orderBy('sequence', 'asc')->get();
			foreach ($InquiryQuestions as $key => $value) {
				$InquiryQuestion = InquiryQuestion::find($value->id);
				$InquiryQuestion->sequence = $key;
				$InquiryQuestion->save();

			}
		}
		$response = successRes("Successfully delete Inquiry Question");
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function orderChange(Request $request) {

		$newIndex = (int) $request->index;
		$oldIndex = (int) $request->old_index;

		$numberOfrecord = abs($newIndex - $oldIndex);
		if ($oldIndex > $newIndex) {

			$InquiryQuestions = InquiryQuestion::select('id', 'sequence')->where('status', $request->status)->where('sequence', '>=', $newIndex)->where('sequence', '<=', $oldIndex)->orderBy('sequence', 'asc')->get();
			$InquiryQuestions = json_decode(json_encode($InquiryQuestions), true);
			$InquiryQuestions = array_reverse($InquiryQuestions);

			$noOfrecord = count($InquiryQuestions);

			foreach ($InquiryQuestions as $key => $value) {
				if ($key == 0) {

					$InquiryQuestion = InquiryQuestion::find($value['id']);
					$InquiryQuestion->sequence = $newIndex;
					$InquiryQuestion->save();

				} else {

					$InquiryQuestion = InquiryQuestion::find($value['id']);
					$InquiryQuestion->sequence = $oldIndex;
					$InquiryQuestion->save();
					$oldIndex--;

				}

			}

		} else if ($oldIndex < $newIndex) {

			$InquiryQuestions = InquiryQuestion::select('id', 'sequence')->where('status', $request->status)->where('sequence', '<=', $newIndex)->where('sequence', '>=', $oldIndex)->orderBy('sequence', 'asc')->get();
			$InquiryQuestions = json_decode(json_encode($InquiryQuestions), true);

			//$InquiryQuestions = array_reverse($InquiryQuestions);

			$noOfrecord = count($InquiryQuestions);

			foreach ($InquiryQuestions as $key => $value) {
				if ($key == 0) {

					$InquiryQuestion = InquiryQuestion::find($value['id']);
					$InquiryQuestion->sequence = $newIndex;
					$InquiryQuestion->save();

				} else {

					$InquiryQuestion = InquiryQuestion::find($value['id']);
					$InquiryQuestion->sequence = $oldIndex;
					$InquiryQuestion->save();
					$oldIndex++;

				}

			}

		}

	}

	public function dependedQuestion(Request $request) {

		if ($request->inquiry_questions_id != 0) {

			$InquiryQuestion = InquiryQuestion::find($request->inquiry_questions_id);

		}
		$q = $request->q;

		$results = array();
		$results = InquiryQuestion::select('id', 'question as text');
		$results->where('type', '!=', 0);
		$results->where('type', '!=', 2);
		$results->where('type', '!=', 5);
		$results->where('is_depend_on_answer', 0);
		//$results->where('is_required', 0);
		$results->where('question', 'like', "%" . $q . "%");
		if ($request->inquiry_questions_id != 0) {

			$results->where('id', '!=', $InquiryQuestion->id);

			$results->where(function ($query) use ($q, $InquiryQuestion) {

				$query->where(function ($query2) use ($q, $InquiryQuestion) {
					$query2->where('status', $InquiryQuestion->status);
					$query2->where('sequence', '<', $InquiryQuestion->sequence);

				});
				$query->orWhere(function ($query) use ($q, $InquiryQuestion) {
					$query->where('status', '<', $InquiryQuestion->status);

				});

			});

		} else {

			$results->where('status', '<=', $request->status);

		}
		$results->limit(5);
		$results = $results->get();
		$finalSearch = array();
		// $cFinalSearch = count($finalSearch);
		// $finalSearch[$cFinalSearch]['id'] = 0;
		// $finalSearch[$cFinalSearch]['text'] = "--- Select Depended Question---";

		foreach ($results as $key => $value) {

			$cFinalSearch = count($finalSearch);
			$finalSearch[$cFinalSearch]['id'] = $value->id;
			$finalSearch[$cFinalSearch]['text'] = "#" . $value->id . ". " . $value->text;

		}

		$response = array();
		$response['results'] = $finalSearch;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');

	}
	public function dependedQuestionAnswer(Request $request) {

		$InquiryQuestion = InquiryQuestion::find($request->inquiry_questions_id);
		$results = array();
		if ($InquiryQuestion) {

			if ($InquiryQuestion->type == 1 || $InquiryQuestion->type == 4 || $InquiryQuestion->type == 6) {

				$results = InquiryQuestionOption::select('id', 'option as text');
				$results->where('inquiry_question_id', $InquiryQuestion->id);
				$results->where('option', 'like', "%" . $request->q . "%");

				$results->limit(10);
				$results = $results->get();

			} else if ($InquiryQuestion->type == 3) {

				$results[0]['id'] = 1;
				$results[0]['text'] = "Checked";

				$results[1]['id'] = 0;
				$results[1]['text'] = "Not Checked";

			}

		}

		$response = array();
		$response['results'] = $results;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');

	}

}
