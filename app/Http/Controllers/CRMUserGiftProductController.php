<?php

namespace App\Http\Controllers;

use App\Models\Architect;
use App\Models\Electrician;
use App\Models\GiftCategory;
use App\Models\GiftProduct;
use App\Models\Parameter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CRMUserGiftProductController extends Controller {

	public function __construct() {
		$this->middleware(function ($request, $next) {
			$tabCanAccessBy = array(202, 302);
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

		$GiftCategory = GiftCategory::where('status', 1)->where('type', Auth::user()->type)->get();
		$accessCategoryId = array();
		$accessCategoryId[] = 0;
		foreach ($GiftCategory as $key => $value) {
			$accessCategoryId[] = $value->id;
		}

		$GiftProduct = GiftProduct::query();
		$GiftProduct->whereIn('gift_category_id', $accessCategoryId);
		if ($category != "0") {
			$GiftProduct->where('gift_category_id', $category);
		}
		$GiftProduct->where('status', 1);
		$GiftProduct = $GiftProduct->get();

		if (Auth::user()->type == 202) {

			$Architect = Architect::where('user_id', Auth::user()->id)->first();
			$point_data = $Architect;

		} else if (Auth::user()->type == 302) {

			$Electrician = Electrician::where('user_id', Auth::user()->id)->first();
			$point_data = $Electrician;

		}

		$data = array();
		$data['title'] = "Gift Product";
		$data['products'] = $GiftProduct;
		$data['category'] = $GiftCategory;
		$data['category_name'] = $categoryName;
		$data['point_data'] = $point_data;
		return view('crm/architect/gift_products', compact('data'));
	}

	public function detail(Request $request) {

		$GiftProduct = GiftProduct::find($request->product);

		if ($GiftProduct) {

			$GiftCategory = GiftCategory::find($GiftProduct->gift_category_id);
			if ($GiftCategory->type == Auth::user()->type) {

				if (Auth::user()->type == 202) {

					$Architect = Architect::where('user_id', Auth::user()->id)->first();
					$point_data = $Architect;

				} else if (Auth::user()->type == 302) {

					$Electrician = Electrician::where('user_id', Auth::user()->id)->first();
					$point_data = $Electrician;

				}

				$data = array();
				$data['title'] = $GiftProduct->name;
				$data['product'] = $GiftProduct;
				$data['category'] = $GiftCategory;
				$data['point_data'] = $point_data;
				return view('crm/architect/gift_product_detail', compact('data'));

			}

		}

	}

	public function cash(Request $request) {

		$isElectrician = isElectrician();
		$isArchitect = isArchitect();

		if ($isArchitect == 1) {
			$Parameter = Parameter::where('code', 'point-value-architect')->first();
		} else if ($isElectrician == 1) {
			$Parameter = Parameter::where('code', 'point-value-electrician')->first();
		} else {
			echo "Something went wrong";
			die;
		}

		$pointValue = $Parameter->name_value;

		if (Auth::user()->type == 202) {

			$Architect = Architect::where('user_id', Auth::user()->id)->first();
			$point_data = $Architect;

		} else if (Auth::user()->type == 302) {

			$Electrician = Electrician::where('user_id', Auth::user()->id)->first();
			$point_data = $Electrician;

		}
		$GiftCategory = GiftCategory::where('status', 1)->where('type', Auth::user()->type)->get();

		$data = array();
		$data['title'] = "Cash";
		$data['point_value'] = $pointValue;
		$data['point_data'] = $point_data;
		$data['category'] = $GiftCategory;

		return view('crm/architect/gift_product_cash', compact('data'));

	}

}