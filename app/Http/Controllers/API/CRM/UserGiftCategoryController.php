<?php
namespace App\Http\Controllers\API\CRM;
use App\Http\Controllers\Controller;
use App\Models\GiftCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserGiftCategoryController extends Controller {

	public function __construct() {
		$this->middleware(function ($request, $next) {
			$tabCanAccessBy = array(202, 302);
			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				$response = errorRes("Invalid access", 401);
				return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');
			}
			return $next($request);
		});
	}

	public function ajax(Request $request) {

		$searchColumns = array(
			0 => 'gift_categories.id',
			1 => 'gift_categories.name',

		);

		$sortingColumns = array(
			0 => 'gift_categories.id',
			1 => 'gift_categories.name',
			2 => 'gift_categories.type',
			3 => 'gift_categories.status',

		);

		$selectColumns = array(
			0 => 'gift_categories.id',
			1 => 'gift_categories.name',
			2 => 'gift_categories.type',
			3 => 'gift_categories.status',

		);

		$data = GiftCategory::select('id', 'name')->where('type', Auth::user()->type)->where('status', 1)->get();
		$response = successRes("Gift Category");
		$response['data'] = $data;
		return response()->json($response, $response['status_code'])->header('Content-Type', 'application/json');

		return $jsonData;
	}

}