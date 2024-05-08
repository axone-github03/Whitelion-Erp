<?php

namespace App\Http\Controllers;

use App\Models\CRMHelpDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CRMUserHelpDocumentController extends Controller {

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

		$query = CRMHelpDocument::query();
		$query->where('status', 1);
		$query->where('type', Auth::user()->type);
		$query->limit(30);
		$query->orderBy('publish_date_time', "desc");
		$helpDocumentList = $query->get();

		$data = array();
		$data['title'] = "Help Document";
		$data['help_document_list'] = $helpDocumentList;
		return view('crm/architect/help_document', compact('data'));
	}

}