<?php

namespace App\Http\Controllers\AppSetting;

use App\Http\Controllers\Controller;
use App\Models\ReviewMaster;
use App\Models\CityList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ReviewController extends Controller
{

    public function __construct()
    {

        $this->middleware(function ($request, $next) {

            $tabCanAccessBy = array(0, 1);

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $data = array();
        $data['title'] = "Review Master";
        return view('app_setting/review/review', compact('data'));
    }


    function ajax(Request $request)
    {
        //DB::enableQueryLog();

        $searchColumns = array(
            'review_master.id',
            'review_master.name',
            'review_master.review',
        );

        $columns = array(
            'review_master.id',
            'review_master.name',
            'review_master.designation',
            'review_master.review',
            'review_master.is_fix',
            'review_master.status',
            'review_master.image',
            'review_master.country_id',
            'review_master.state_id',
            'review_master.city_id',
            'city_list.name as city_name',
        );

        $recordsTotal = ReviewMaster::count();
        $recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

        $query = ReviewMaster::query();
        $query->select($columns);
        $query->leftJoin('city_list', 'city_list.id', '=', 'review_master.city_id');
        $query->limit($request->length);
        $query->offset($request->start);
        $query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
        $query->orderBy('review_master.is_fix', 'DESC');
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
        // echo "<pre>";
        // print_r(DB::getQueryLog());
        // die;

        $data = json_decode(json_encode($data), true);
        $data2 = json_decode(json_encode($data), true);

        if ($isFilterApply == 1) {
            $recordsFiltered = count($data);
        }

        foreach ($data as $key => $value) {
            $data[$key]['id'] = '<div class="avatar-xs"><span class="avatar-title rounded-circle">'.$value['id'].'</span></div>';

            $image = 'https://erp.whitelion.in/assets/images/favicon.ico';
            if($value['image'] == null){
                $image = 'https://erp.whitelion.in/assets/images/favicon.ico';
            }else{
                $image = getSpaceFilePath($value['image']);
            }
            $data[$key]['image'] = '<div class="text-center"><img class="product-img" src="'.$image.'" /></div>';

            $data[$key]['review'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . highlightString($value['name'],$search_value) .'('.highlightString($value['designation'],$search_value). ')</a></h5><p>'.highlightString($value['review'],$search_value).'</p>';

            $data[$key]['city'] = '<p>'.highlightString($value['city_name'],$search_value).'</p>';

            $data[$key]['isactive'] = getMainMasterStatusLable($value['status']);

            $uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

            $uiAction .= '<li class="list-inline-item px-2">';
            $uiAction .= '<a onclick="editView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
            $uiAction .= '</li>';

            // $uiAction .= '<li class="list-inline-item px-2">';
            // $uiAction .= '<a onclick="deleteWarning(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Delete"><i class="bx bx-trash-alt"></i></a>';
            // $uiAction .= '</li>';

            $uiAction .= '</ul>';

            $data[$key]['action'] = $uiAction;
        }

        $jsonData = array(
            "draw" => intval($request['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal" => intval($recordsTotal), // total number of records
            "recordsFiltered" => intval($recordsFiltered), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data, // total data array
        );

        return $jsonData;
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q_master_id' => ['required'],
            'q_master_name' => ['required'],
            'q_master_designation' => ['required'],
            'q_master_city_id' => ['required'],
            'q_master_review' => ['required'],
            'q_master_status' => ['required'],
        ]);

        if ($validator->fails()) {

            $response = array();
            $response['status'] = 0;
            $response['msg'] = $validator->errors()->first();
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {

            $uploadedFile1 = "";

            if ($request->hasFile('q_master_image')) {

                $validator = Validator::make($request->all(), [
                    'q_master_image' => ['required'],
                ]);

                if ($validator->fails()) {
                    $response = array();
                    $response['status'] = 0;
                    $response['msg'] = $validator->errors()->first();
                    $response['statuscode'] = 400;
                    $response['data'] = $validator->errors();

                    return response()->json($response)->header('Content-Type', 'application/json');
                } else {



                    $folderPathImage = '/review';
                    $fileObject1 = $request->file('q_master_image');

                    $extension = $fileObject1->getClientOriginalExtension();
                    $fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

                    $destinationPath = public_path($folderPathImage);

                    $fileObject1->move($destinationPath, $fileName1);

                    if (File::exists(public_path($folderPathImage . "/" . $fileName1))) {

                        $uploadedFile1 = $folderPathImage . "/" . $fileName1;

                        $spaceUploadResponse = uploadFileOnSpaces(public_path($uploadedFile1), $uploadedFile1);
                        if ($spaceUploadResponse != 1) {
                            $uploadedFile1 = "";
                        } else {
                            unlink(public_path($uploadedFile1));
                        }
                    }
                }
            }

            if ($request->q_master_id != 0) {
                $MainMaster = ReviewMaster::find($request->q_master_id);
                $MainMaster->updateby = Auth::user()->id;
                $MainMaster->updateip = $request->ip();
            } else {
                $MainMaster = new ReviewMaster();
                $MainMaster->entryby = Auth::user()->id;
                $MainMaster->entryip = $request->ip();
            }

            $MainMaster->name = $request->q_master_name;
            $MainMaster->designation = $request->q_master_designation;
            $objCity = CityList::find($request->q_master_city_id);
            $MainMaster->country_id = $objCity->country_id;
            $MainMaster->state_id = $objCity->state_id;
            $MainMaster->city_id = $request->q_master_city_id;
            $MainMaster->review = $request->q_master_review;
            if($request->q_master_isfix == "on"){
                $MainMaster->is_fix = 1;
            }else{
                $MainMaster->is_fix = 0;
            }
            $MainMaster->status = $request->q_master_status;
            $MainMaster->source = 'WEB';

            if ($uploadedFile1 != "") {
                $MainMaster->image = $uploadedFile1;
            }

            $MainMaster->save();


            if ($MainMaster) {
                
                if ($request->q_master_id != 0) {

                    $response = successRes("Successfully saved Review");

                    $debugLog = array();
                    $debugLog['name'] = "review-master-edit";
                    $debugLog['description'] = "Review #" . $MainMaster->id . "(" . $MainMaster->itemname . ")" . " has been updated ";
                    saveDebugLog($debugLog);
                } else {
                    $response = successRes("Successfully added Review");

                    $debugLog = array();
                    $debugLog['name'] = "review-master-add";
                    $debugLog['description'] = "Review #" . $MainMaster->id . "(" . $MainMaster->itemname . ") has been added ";
                    saveDebugLog($debugLog);
                }
            }


            // }

            return response()->json($response)->header('Content-Type', 'application/json');
        }
    }

    public function detail(Request $request)
    {

        $MainMaster = ReviewMaster::find($request->id);

        if ($MainMaster) {
            $response = successRes("Successfully get quotation item master");

            $CityList = CityList::select('city_list.id', 'city_list.name as city_list_name', 'state_list.name as state_list_name');
		    $CityList->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
		    $CityList->where('city_list.id', $MainMaster->city_id);
            $cityText = '';
		    $CityList = $CityList->first();
		    if ($CityList) {
		    	$cityText = $CityList->city_list_name . ", " . $CityList->state_list_name . ", India";
		    }


            $data['MainMaster'] = $MainMaster;
            $data['MainMaster']['city_text'] = $cityText;

            $data['MainMaster']['image'] = getSpaceFilePath($MainMaster->image);
            $response['data'] = $data;
        } else {
            $response = errorRes("Invalid id");
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

   
}
