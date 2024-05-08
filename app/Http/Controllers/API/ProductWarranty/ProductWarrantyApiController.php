<?php

namespace App\Http\Controllers\API\ProductWarranty;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Wlmst_ProductWarranty;
use App\Models\DebugLog;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

// use Illuminate\Http\Request;

class ProductWarrantyApiController extends Controller
{
	public function PostProductWarrantyRegisterSave(Request $request)
	{

		try {
			$response = array();

			$validator = Validator::make($request->all(), [
				'fullname' => ['required'],
				'mobile_number' => ['required'],
				'email_id' => ['required', 'email'],
				'house_number' => ['required'],
				'society_name' => ['required'],
				'area_name' => ['required'],
				'city_name' => ['required'],
				'invoice_image' => ['required'],
				'ischeck_terms' => ['required'],
			]);

			if ($validator->fails()) {
				$response = quoterrorRes(0, 400, 'Please Check All Field Value');
				$response['error'] = $validator->errors();
			} else {

				$alreadyName = Wlmst_ProductWarranty::query();
				$alreadyName->where('fullname', $request->fullname);
				$alreadyName = $alreadyName->first();

				$alreadyMobile = Wlmst_ProductWarranty::query();
				$alreadyMobile->where('mobile', $request->mobile_number);
				$alreadyMobile = $alreadyMobile->first();

				$alreadyEmail = Wlmst_ProductWarranty::query();
				$alreadyEmail->where('email', $request->email_id);
				$alreadyEmail = $alreadyEmail->first();

				if ($alreadyName) {
					$response = quoterrorRes(0, 400, 'already name exits, Try with another name');
				} else if ($alreadyMobile) {
					$response = quoterrorRes(0, 400, 'already mobile exits, Try with another mobile');
				} else if ($alreadyEmail) {
					$response = quoterrorRes(0, 400, 'already email exits, Try with another email');
				} else if (!$request->hasFile('invoice_image')) {
					$response = quoterrorRes(0, 400, 'please upload invoice image');
				} else {

					$uploadedFile1 = "";

					if ($request->hasFile('invoice_image')) {

						$folderPathImage = '/product_warranty_invoice';
						$fileObject1 = $request->file('invoice_image');

						$extension = $fileObject1->getClientOriginalExtension();
						$fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

						$destinationPath = public_path($folderPathImage);

						$fileObject1->move($destinationPath, $fileName1);

						if (File::exists(public_path($folderPathImage . "/" . $fileName1))) {

							$uploadedFile1 = $folderPathImage . "/" . $fileName1;

								$spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1); //Live
								if ($spaceUploadResponse != 1) {
									$uploadedFile1 = "";
								} else {
									unlink(public_path($uploadedFile1));
								}
						}
					}

					$ClientMaster = new Wlmst_ProductWarranty();
					$ClientMaster->entryby = '0';
					$ClientMaster->entryip = $request->ip();

					$ClientMaster->fullname = $request->fullname;
					$ClientMaster->mobile = $request->mobile_number;
					$ClientMaster->email = $request->email_id;
					$ClientMaster->address_houseno = $request->house_number;
					$ClientMaster->address_society = $request->society_name;
					$ClientMaster->address_area = $request->area_name;
					$ClientMaster->address_city = $request->city_name;
					$ClientMaster->invoice_image = $uploadedFile1;
					$ClientMaster->source = 'WEB';

					$ClientMaster->save();
					if ($ClientMaster) {
						$response = quotsuccessRes();
					} else {
						$response = quoterrorRes(0, 400, 'please contact to admin');
					}
				}
			}
			return response()->json($response)->header('Content-Type', 'application/json');
		} catch (\Exception $e) {
			return response()->json($e->getMessage())->header('Content-Type', 'application/json');
		}
	}
}
