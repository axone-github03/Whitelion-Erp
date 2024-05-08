<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

//
class ZohoController extends Controller {

	public function index(Request $request) {

		$fileContent = file_put_contents('/var/www/html/encodework-apps/whitelion-erp/public/zoholog/' . time() . ".json", json_encode($request->all()));

		$response = successRes("Successfully called API");
		return response()->json($response)->header('Content-Type', 'application/json');

	}

}