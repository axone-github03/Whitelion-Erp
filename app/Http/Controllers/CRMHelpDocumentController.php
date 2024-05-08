<?php

namespace App\Http\Controllers;

use App\Models\CRMHelpDocument;
use App\Models\User;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CRMHelpDocumentController extends Controller {

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

		$CRMUserType = CRMUserType();
		$data = array();
		$data['title'] = "Help Document";
		$data['crm_user_type'] = $CRMUserType;
		return view('crm/help_document', compact('data'));
	}

	public function ajax(Request $request) {

		$searchColumns = array(
			0 => 'crm_help_document.id',
			1 => 'crm_help_document.title',

		);

		$sortingColumns = array(
			0 => 'crm_help_document.id',
			1 => 'crm_help_document.title',
			2 => 'crm_help_document.type',
			3 => 'crm_help_document.status',

		);

		$selectColumns = array(
			'crm_help_document.id',
			'crm_help_document.title',
			'crm_help_document.status',
			'crm_help_document.publish_date_time',
			'crm_help_document.file_name',
			'crm_help_document.type',

		);

		$query = CRMHelpDocument::query();
		$recordsTotal = $query->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.
		$query = CRMHelpDocument::query();
		$query->select($selectColumns);
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

		$viewData = array();
		$CRMUserType = CRMUserType();
		foreach ($data as $key => $value) {

			$viewData[$key] = array();
			$viewData[$key]['id'] = highlightString($value['id'],$search_value);

			$viewData[$key]['title'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . highlightString($value['title'],$search_value) . '</a></h5>';

			$viewData[$key]['download'] = '<a class="btn btn-primary waves-effect waves-light" target="_blank" href="' . getSpaceFilePath($value['file_name']) . '" ><i class="bx bx-download font-size-16 align-middle me-2"></i>Download</a>';

			$viewData[$key]['publish_date_time'] = highlightString(convertDateTime($value['publish_date_time']),$search_value);

			$viewData[$key]['type'] = highlightString($CRMUserType[$value['type']]['another_name'],$search_value);

			$viewData[$key]['status'] = getCRMHelpDocumentStatusLable($value['status']);

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
			$uiAction .= '</li>';
			$uiAction .= '</ul>';
			$viewData[$key]['action'] = $uiAction;

		}

		$jsonData = array(
			"draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
			"recordsTotal" => intval($recordsTotal), // total number of records
			"recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
			"data" => $viewData, // total data array

		);
		return $jsonData;
	}

	public function save(Request $request) {

		$rules = array();
		$rules['crm_help_document_id'] = 'required';
		$rules['crm_help_document_title'] = 'required';
		$rules['crm_help_document_status'] = 'required';
		$rules['crm_help_document_publish_date_time'] = 'required';
		$rules['crm_help_document_type'] = 'required';

		if ($request->crm_help_document_id == 0) {
			$rules['crm_help_document_file'] = 'required';
		}

		$validator = Validator::make($request->all(), $rules);
		if ($validator->fails()) {
			$response = array();
			$response['status'] = 0;
			$response['msg'] = "The request could not be understood by the server due to malformed syntax";
			$response['statuscode'] = 400;
			$response['data'] = $validator->errors();

			// return redirect()->back()->with("error", "Something went wrong with validation");

		} else {

			$uploadedFile1 = "";

			if ($request->hasFile('crm_help_document_file')) {

				$folderPathImage = '/s/crm-help-document';
				$fileObject1 = $request->file('crm_help_document_file');
				$extension = $fileObject1->getClientOriginalExtension();
				$fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;
				$destinationPath = public_path($folderPathImage);

				$fileObject1->move($destinationPath, $fileName1);

				if (File::exists(public_path($folderPathImage . "/" . $fileName1))) {

					$uploadedFile1 = $folderPathImage . "/" . $fileName1;

					//START UPLOAD FILE ON SPACES

					$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
					if ($spaceUploadResponse != 1) {
						$uploadedFile1 = "";

					} else {
						unlink(public_path($uploadedFile1));
					}
					//END UPLOAD FILE ON SPACES

				}
			}

			if ($request->crm_help_document_id == 0) {

				$CRMHelpDocument = new CRMHelpDocument();
				$CRMHelpDocument->file_name = $uploadedFile1;

			} else {

				$CRMHelpDocument = CRMHelpDocument::find($request->crm_help_document_id);
				if ($uploadedFile1 != "") {
					$CRMHelpDocument->file_name = $uploadedFile1;

				}

			}
			$CRMHelpDocument->title = $request->crm_help_document_title;
			$CRMHelpDocument->publish_date_time = $request->crm_help_document_publish_date_time;
			$CRMHelpDocument->status = $request->crm_help_document_status;
			$CRMHelpDocument->type = $request->crm_help_document_type;
			$CRMHelpDocument->save();

			if ($CRMHelpDocument) {

				if ($request->crm_help_document_id != 0) {

					$response = successRes("Successfully saved gift category");

					$debugLog = array();
					$debugLog['name'] = "gift-category-edit";
					$debugLog['description'] = "gift category #" . $CRMHelpDocument->id . "(" . $CRMHelpDocument->name . ") has been updated ";
					saveDebugLog($debugLog);

				} else {
					$response = successRes("Successfully added gift category");
					$debugLog = array();
					$debugLog['name'] = "gift-category-add";
					$debugLog['description'] = "gift category #" . $CRMHelpDocument->id . "(" . $CRMHelpDocument->name . ") has been added ";
					saveDebugLog($debugLog);

				}

			}

		}
		return response()->json($response)->header('Content-Type', 'application/json');

	}
	public function detail(Request $request) {

		$CRMHelpDocument = CRMHelpDocument::find($request->id);
		if ($CRMHelpDocument) {

			$CRMHelpDocument->publish_date_time = date('Y-m-d', strtotime($CRMHelpDocument->publish_date_time)) . "T" . date('H:i', strtotime($CRMHelpDocument->publish_date_time));

			$response = successRes("Successfully get main master");
			$response['data'] = $CRMHelpDocument;

		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');

	}
}