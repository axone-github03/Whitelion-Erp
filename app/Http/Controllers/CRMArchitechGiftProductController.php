<?php

namespace App\Http\Controllers;

use App\Models\GiftCategory;
use App\Models\GiftProduct;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CRMArchitechGiftProductController extends Controller {

	public function __construct() {
		$this->middleware(function ($request, $next) {
			$tabCanAccessBy = array(202);
			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}
			return $next($request);
		});
	}

	public function index(Request $request) {

		$category = isset($request->category) ? $request->category : '0';
		$categoryName = "All";
		if ($category != "0") {

			$GiftCategory = GiftCategory::find($category);
			if ($GiftCategory) {
				$categoryName = $GiftCategory->name;
			}

		}

		$GiftProduct = GiftProduct::query();
		if ($category != "0") {
			$GiftProduct->where('gift_category_id', $category);
		}
		$GiftProduct->where('status', 1);
		$GiftProduct = $GiftProduct->get();
		$GiftCategory = GiftCategory::where('status', 1)->get();

		$data = array();
		$data['title'] = "Gift Product";
		$data['products'] = $GiftProduct;
		$data['category'] = $GiftCategory;
		$data['category_name'] = $categoryName;
		return view('crm/architect/gift_products', compact('data'));
	}

	public function detail(Request $request) {

		$GiftProduct = GiftProduct::find($request->product);
		if ($GiftProduct) {

			$GiftCategory = GiftCategory::find($GiftProduct->gift_category_id);

			$data = array();
			$data['title'] = $GiftProduct->name;
			$data['product'] = $GiftProduct;
			$data['category'] = $GiftCategory;
			return view('crm/architect/gift_product_detail', compact('data'));

		}

	}

}