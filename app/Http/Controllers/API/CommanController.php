<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Lead;
use App\Models\LeadContact;
use App\Models\Wlmst_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;

class CommanController extends Controller {

	public function getChannelPartnerTypes() {

		$data = getChannelPartners();
		$data = array_values($data);
		foreach ($data as $key => $value) {

			unset($data[$key]['lable']);
			unset($data[$key]['key']);
			unset($data[$key]['url']);
			unset($data[$key]['url_view']);
			unset($data[$key]['url_sub_orders']);
			unset($data[$key]['can_login']);
			unset($data[$key]['inquiry_tab']);

		}
		$response = successRes("Get Channel Partner Type");
		$response['data'] = $data;
		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');

	}

	public function createDeal(Request $request) {
		$validator = Validator::make($request->all(), [
            'q_deal_excel' => ['required'],
        ]);
        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = "The request could not be understood by the server due to malformed syntax";
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $the_file = $request->file('q_deal_excel');
			try {
                $spreadsheet = IOFactory::load($the_file->getRealPath());
                $sheet        = $spreadsheet->getActiveSheet();
                $row_limit    = $sheet->getHighestDataRow();
                $row_range    = range(2, $row_limit);
				
                $data = array();
                foreach ($row_range as $row) {
					$wlmst_client = new Wlmst_Client();
                    $wlmst_client->name = $sheet->getCell('A' . $row)->getValue();
                    $wlmst_client->email = "";
                    $wlmst_client->mobile = $sheet->getCell('B' . $row)->getValue();
                    $wlmst_client->address = $sheet->getCell('C' . $row)->getValue() . ', ' . $sheet->getCell('D' . $row)->getValue() . ', ' . $sheet->getCell('E' . $row)->getValue();
                    $wlmst_client->isactive = 1;
                    $wlmst_client->remark = 0;
                    $wlmst_client->save();

					$Lead = new Lead();
					$Lead->first_name = $sheet->getCell('A' . $row)->getValue();
					$Lead->last_name = "";
					$Lead->email = "";
					$Lead->customer_id = $wlmst_client->id;
					$Lead->main_contact_id = 0;
					$Lead->phone_number = $sheet->getCell('B' . $row)->getValue();
					$Lead->status = 103;
					if($sheet->getCell('C' . $row)->getValue() != "") {
						$Lead->house_no = $sheet->getCell('C' . $row)->getValue() != "";
						$Lead->meeting_house_no = $sheet->getCell('C' . $row)->getValue() != "";
					} else {
						$Lead->house_no = "";
						$Lead->meeting_house_no = "";
					}

					if($sheet->getCell('D' . $row)->getValue() != "") {
						$Lead->addressline1 = $sheet->getCell('D' . $row)->getValue();
						$Lead->meeting_addressline1 = $sheet->getCell('D' . $row)->getValue();
					} else {
						$Lead->addressline1 = "";
						$Lead->meeting_addressline1 = "";
					}

					if($sheet->getCell('E' . $row)->getValue() != "") {
						$Lead->area = $sheet->getCell('E' . $row)->getValue();
						$Lead->meeting_area = $sheet->getCell('E' . $row)->getValue();
					} else {
						$Lead->area = "";
						$Lead->meeting_area = "";
					}
					$Lead->pincode = "";
					$Lead->meeting_pincode = "";
					$Lead->city_id = 0;
					$Lead->meeting_city_id = 0;
					$Lead->site_stage = 0;
					$Lead->site_type = 0;
					$Lead->bhk = 0;
					$Lead->sq_foot = 0;
					$Lead->want_to_cover = 0;
					$Lead->source_type = 'user-202';
					$Lead->source = 257;
					$Lead->budget = 0;
					$Lead->closing_date_time = date('Y-m-d H:i:s', strtotime('01-01-2024' . date('H:i:s')));
					$Lead->competitor = 0;
					$Lead->architect = 257;
					$Lead->is_deal = 1;
					$Lead->account_user_id = 0;
					$Lead->assigned_to = 29;
					$Lead->created_by = 1;
					$Lead->entry_source = "WEB";
					$Lead->entryip = $request->ip();
					$Lead->updated_by = 1;
					$Lead->update_source = "WEB";
					$Lead->updateip = $request->ip();
					$Lead->save();

					if($Lead) {
						$LeadMainContact = new LeadContact();
						$LeadMainContact->lead_id = $Lead->id;
						$LeadMainContact->contact_tag_id = 1;
						$LeadMainContact->first_name = $Lead->first_name;
						$LeadMainContact->last_name = "";
						$LeadMainContact->phone_number = $Lead->phone_number;
						$LeadMainContact->alernate_phone_number = "";
						$LeadMainContact->email = '-';
						$LeadMainContact->type = 0;
						$LeadMainContact->type_detail = 0;
						$LeadMainContact->status = 1;
						$LeadMainContact->save();

						$Lead->main_contact_id = $LeadMainContact->id;
						$Lead->save();

						$LeadContact = new LeadContact();
						$LeadContact->lead_id = $Lead->id;
						$LeadContact->contact_tag_id = 0;
						$LeadContact->first_name = "hiral";
						$LeadContact->last_name = "jani";
						$LeadContact->phone_number = "9033808918";
						$LeadContact->alernate_phone_number = "";
						$LeadContact->email = "heerenterprise2110@gmail.com";
						$LeadContact->type = 202;
						$LeadContact->type_detail = "user-202-257";
						$LeadContact->status = 1;
						$LeadContact->save();

						$timeline = array();
                        $timeline['lead_id'] = $Lead->id;
                        $timeline['type'] = "lead-generate";
                        $timeline['reffrance_id'] = $Lead->id;
                        $timeline['description'] = "Lead created by " . Auth::user()->first_name . " " . Auth::user()->last_name;
                        $timeline['source'] = "WEB";
                        saveLeadTimeline($timeline);
					}
                }

				// $response = successRes('Data Imported Successfully');
                $debugLog = array();
                $debugLog['name'] = "deal-create-manually-data-excel-upload";
                $debugLog['description'] = "Deal Manually Create";
                saveDebugLog($debugLog);

				$response = successRes();
				$response['data'] = $row_range;
            } catch (Exception $e) {
                $response = errorRes($e->getMessage());
            }
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}
}