<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

//use App\Models\User;

//use Illuminate\Support\Facades\Hash;

class IncentiveController extends Controller {

	public function __construct() {

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');

			}

			return $next($request);

		});

	}

	public function salePerson() {

		$data = array();
		$data['title'] = "Sale Person - Incentive";
		return view('incentive/sale_person', compact('data'));

	}

	public function channelPartner() {

		$data = array();
		$data['title'] = "Channel Partner - Incentive";
		return view('incentive/channel_partner', compact('data'));

	}

}