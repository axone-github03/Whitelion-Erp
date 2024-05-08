<?php

namespace App\Http\Controllers\CRM\Lead;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CRMSettingFileTag;
use App\Models\LeadFile;
use App\Models\Lead;
use App\Models\LeadTask;
use Illuminate\Support\Facades\File;
use DB;
use Illuminate\Support\Facades\Validator;

class LeadFileController extends Controller
{

    public function __construct()
    {

        $this->middleware(function ($request, $next) {

            $tabCanAccessBy = array(0, 1, 2, 9, 11, 13, 101, 102, 103, 104, 105, 202, 302);

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }

            return $next($request);
        });
    }


    public function save(Request $request)
    {


        $rules = array();
        $rules['lead_file_lead_id'] = 'required';
        // $rules['lead_file_file_name'] = 'required';
        $rules['lead_file_tag_id'] = 'required';
        $customMessage = array();
        $customMessage['lead_file_lead_id.required'] = "Invalid parameters";


        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {

            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            if ($request->lead_file_tag_id == 3) {

                for ($i = 0; $i < $request->count_of_bill; $i++) {
                    if ($request->input("bill_number_$i") == '') {

                        $response = errorRes('please enter bill number');
                    } else {

                        $chkLeadFile = LeadFile::query()->where('file_tag_id', $request->input("bill_number_$i"))->first();
                        if ($chkLeadFile) {
                            $response = errorRes('This bill number already addeed');
                        }
                    }
                }

                $isBillAttech = 0;
                $File_Ids = array();
                $billNo = '';
                for ($i = 0; $i < $request->count_of_bill; $i++) {
                    $billNo = $request->input("bill_number_$i");
                    $uploadedFile1 = array();
                    $fileSize = 0;

                    if ($request->hasFile("lead_file_file_name_$i")) {

                        $folderPathofFile = '/s/lead-files/';
                        if (!is_dir(public_path($folderPathofFile))) {
                            mkdir(public_path($folderPathofFile));
                        }

                        $folderPathofFile = '/s/lead-files/' . date('Y');

                        if (!is_dir(public_path($folderPathofFile))) {
                            mkdir(public_path($folderPathofFile));
                        }

                        $folderPathofFile = '/s/lead-files/' . date('Y') . "/" . date('m');
                        if (!is_dir(public_path($folderPathofFile))) {
                            mkdir(public_path($folderPathofFile));
                        }
                        foreach ($request->file("lead_file_file_name_$i") as $file) {

                            $fileObject1 = $file;
                            $extension = $fileObject1->getClientOriginalExtension();

                            $fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

                            $destinationPath = public_path($folderPathofFile);
                            $fileObject1->move($destinationPath, $fileName1);

                            if (File::exists(public_path($folderPathofFile . "/" . $fileName1))) {

                                $fileName1 = $folderPathofFile . "/" . $fileName1;

                                $fileSize = filesize(public_path($fileName1));

                                $spaceUploadResponse = uploadFileOnSpaces(public_path($fileName1), $fileName1);
                                if ($spaceUploadResponse != 1) {
                                    $fileName1 = "";
                                } else {
                                    unlink(public_path($fileName1));
                                    $uploadedFile1[] = $fileName1;
                                }
                            }
                        }

                        $LeadFile = new LeadFile();
                        $LeadFile->uploaded_by = Auth::user()->id;
                        $LeadFile->file_size = $fileSize;
                        $LeadFile->name = $fileName1;
                        $LeadFile->lead_id = $request->lead_file_lead_id;
                        $LeadFile->file_tag_id = $request->lead_file_tag_id;
                        // $leadFile->bill_number = '$billNo';
                        $LeadFile->save();
                        if ($LeadFile) {
                            $isBillAttech = 1;
                            array_push($File_Ids, $LeadFile->id);
                            $tag = CRMSettingFileTag::find($LeadFile->file_tag_id)['name'];
                            $timeline = array();
                            $timeline['lead_id'] = $LeadFile->lead_id;
                            $timeline['type'] = "file-upload";
                            $timeline['reffrance_id'] = $LeadFile->lead_id;
                            $timeline['description'] = "" . $tag . " Upload";
                            $timeline['source'] = "WEB";
                            saveLeadTimeline($timeline);
                        }
                    }
                }

                if ($isBillAttech == 1) {

                    $current_date = date('Y-m-d H:i:s');
                    $Plus_three_day = date('Y-m-d H:i:s');
                    $lead = Lead::find($LeadFile->lead_id);
                    // START COMPANY ADMIN TASK ASSIGN
                    $LeadTask = new LeadTask();
                    $LeadTask->lead_id = $lead->id;
                    $LeadTask->user_id = 5867;
                    $LeadTask->assign_to = 5867;
                    $LeadTask->task = "Verified Uploaded Bill In " . $LeadFile->lead_id . "-" . $lead->first_name . " " . $lead->last_name . "";
                    $LeadTask->due_date_time = $Plus_three_day;
                    $LeadTask->reminder = getReminderTimeSlot($Plus_three_day)[1]['datetime'];
                    $LeadTask->reminder_id = 1;
                    $LeadTask->description = 'Auto Generated Task';
                    $LeadTask->is_notification = 1;
                    $LeadTask->is_autogenerate = 1;
                    $LeadTask->save();
                    // END COMPANY ADMIN TASK ASSIGN
                    if ($LeadTask) {
                        $Lead = Lead::find($LeadTask->lead_id);
                        $Lead->companyadmin_verification = 1;
                        $Lead->save();
                        DB::table('lead_files')->whereIn('lead_files.id', $File_Ids)->update(['reference_id' => $LeadTask->id, 'reference_type' => 'Task']);
                    }
                    $response = successRes("Successfully saved lead file");
                    $response['id'] = $LeadFile->lead_id;
                } else {
                    $response = errorRes("Something went wrong");
                }
            } else {
                $uploadedFile1 = array();
                $fileSize = 0;

                if ($request->hasFile('lead_file_file_name_0')) {

                    $folderPathofFile = '/s/lead-files/';
                    if (!is_dir(public_path($folderPathofFile))) {
                        mkdir(public_path($folderPathofFile));
                    }

                    $folderPathofFile = '/s/lead-files/' . date('Y');

                    if (!is_dir(public_path($folderPathofFile))) {
                        mkdir(public_path($folderPathofFile));
                    }

                    $folderPathofFile = '/s/lead-files/' . date('Y') . "/" . date('m');
                    if (!is_dir(public_path($folderPathofFile))) {
                        mkdir(public_path($folderPathofFile));
                    }


                    foreach ($request->file('lead_file_file_name_0') as $file) {

                        $fileObject1 = $file;
                        $extension = $fileObject1->getClientOriginalExtension();

                        $fileName1 = time() . mt_rand(10000, 99999) . '.' . $extension;

                        $destinationPath = public_path($folderPathofFile);
                        $fileObject1->move($destinationPath, $fileName1);

                        if (File::exists(public_path($folderPathofFile . "/" . $fileName1))) {

                            $fileName1 = $folderPathofFile . "/" . $fileName1;

                            $fileSize = filesize(public_path($fileName1));

                            $spaceUploadResponse = uploadFileOnSpaces(public_path($fileName1), $fileName1);
                            if ($spaceUploadResponse != 1) {
                                $fileName1 = "";
                            } else {
                                unlink(public_path($fileName1));
                                $uploadedFile1[] = $fileName1;
                            }
                        }
                    }
                }

                if ($uploadedFile1 != "") {

                    $File_Ids = array();
                    foreach ($uploadedFile1 as $key => $value) {

                        $LeadFile = new LeadFile();
                        $LeadFile->uploaded_by = Auth::user()->id;
                        $LeadFile->file_size = $fileSize;
                        $LeadFile->name = $value;
                        $LeadFile->lead_id = $request->lead_file_lead_id;
                        $LeadFile->file_tag_id = $request->lead_file_tag_id;
                        $LeadFile->save();



                        if ($LeadFile) {

                            array_push($File_Ids, $LeadFile->id);

                            $tag = CRMSettingFileTag::find($LeadFile->file_tag_id)['name'];

                            $timeline = array();
                            $timeline['lead_id'] = $LeadFile->lead_id;
                            $timeline['type'] = "file-upload";
                            $timeline['reffrance_id'] = $LeadFile->lead_id;
                            $timeline['description'] = "" . $tag . " Upload";
                            $timeline['source'] = "WEB";
                            saveLeadTimeline($timeline);
                        }
                    }

                    $response = successRes("Successfully saved lead file");
                    $response['id'] = $LeadFile->lead_id;
                } else {
                    $response = errorRes("Something went wrong");
                }
            }
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }


    function searchTag(Request $request)
    {
        $searchKeyword = isset($request->q) ? $request->q : "";

        $data = CRMSettingFileTag::select('id', 'name as text');
        $data->where('crm_setting_file_tag.status', 1);
        $data->where('crm_setting_file_tag.name', 'like', "%" . $searchKeyword . "%");
        $data->limit(5);
        $data = $data->get();
        $data = json_decode(json_encode($data), true);
        foreach ($data as $key => $value) {
            if (isAdminOrCompanyAdmin() != 1) {
                if ($request->status == 103 && $request->arc_prime == 1 && $request->tag_id != 3) {
                    $data[$key]['id'] = $value['id'];
                    $data[$key]['text'] = $value['text'];
                } else {
                    if ($value['id'] == 3) {
                        $data[$key]['id'] = "";
                        $data[$key]['text'] = "";
                    } else {
                        $data[$key]['id'] = $value['id'];
                        $data[$key]['text'] = $value['text'];
                    }
                }
            } else {
                if ($request->status == 103 && $request->arc_prime == 1) {

                    $data[$key]['id'] = $value['id'];
                    $data[$key]['text'] = $value['text'];
                } else {
                    if ($value['id'] == 3) {
                        $data[$key]['id'] = "";
                        $data[$key]['text'] = "";
                    } else {
                        $data[$key]['id'] = $value['id'];
                        $data[$key]['text'] = $value['text'];
                    }
                }
            }
        }
        $response = array();
        $response['results'] = $data;
        $response['pagination']['more'] = false;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function delete(Request $request)
    {

        $LeadFile = LeadFile::find($request->id);
        if ($LeadFile) {
            $LeadFile->delete();
        }

        $response = successRes("Successfully delete file");
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function statusChange(Request $request)
    {
        $File = LeadFile::find($request->id);
        if ($File) {
            if ($request->is_active == 1) {
                $File->is_active = 0;
                $File->save();
                if ($File) {
                    $timeline = [];
                    $timeline['lead_id'] = $File['lead_id'];
                    $timeline['type'] = 'lead-status-change';
                    $timeline['reffrance_id'] = $File['lead_id'];
                    $timeline['description'] = 'This File #' . $File['id'] . ' Deactive';
                    $timeline['source'] = '';
                    saveLeadTimeline($timeline);
                    $response = successRes("SuccessFully File Deactive");
                }
            } else if ($request->is_active == 0) {
                $File->is_active = 1;
                $File->save();
                if ($File) {
                    $timeline = [];
                    $timeline['lead_id'] = $File['lead_id'];
                    $timeline['type'] = 'lead-status-change';
                    $timeline['reffrance_id'] = $File['lead_id'];
                    $timeline['description'] = 'This File #' . $File['id'] . ' Active';
                    $timeline['source'] = '';
                    saveLeadTimeline($timeline);
                    $response = successRes("SuccessFully File Active");
                }
            }
            $response['lead_id'] = $File->lead_id;
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }
}
