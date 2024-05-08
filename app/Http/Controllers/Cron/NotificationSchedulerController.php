<?php

namespace App\Http\Controllers\Cron;

use Storage;
use App\Models\Lead;
use App\Models\User;
use App\Models\CityList;
use App\Models\Architect;
use App\Models\Electrician;
use Illuminate\Http\Request;
use App\Models\ChannelPartner;
use Illuminate\Support\Carbon;
use App\Models\Wltrn_Quotation;
use App\Models\Exhibition;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Models\NotificationScheduler;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Whatsapp\WhatsappApiContoller;

class NotificationSchedulerController extends Controller
{
    public function index(Request $request)
    {
        $configrationForNotify = configrationForNotify();


        
        $NotificationScheduler = NotificationScheduler::query();
        $NotificationScheduler->where('status', 0);
        $NotificationScheduler->limit(10);
        $NotificationScheduler = $NotificationScheduler->get();
        if ($NotificationScheduler) {
            foreach ($NotificationScheduler as $key => $value) {
                if ($value['transaction_type'] == 'Email') {
                    if ($value['transaction_name'] == 'Channel Partner') {
                        $ChannelPartner = ChannelPartner::where('user_id', $value['transaction_id'])->first();
                        $User = User::where('id', $value['transaction_id'])->first();

                        $value['firm_name'] = $ChannelPartner->firm_name;
                        $value['first_name'] = $User->first_name;
                        $value['last_name'] = $User->last_name;

                        if ($value['transaction_detail'] == 'emails.channel_partner_advance' || $value['transaction_detail'] == 'emails.channel_partner_deactive') {
                            $value['city_name'] = getCityName($ChannelPartner->d_city_id);
                            $value['channel_partner_type'] = getChannelPartners()[$User->type]['short_name'];
                        } elseif ($value['transaction_detail'] == 'emails.channel_partner_credit') {
                            $value['credit_limit'] = $ChannelPartner->credit_limit;
                            $value['credit_day'] = $ChannelPartner->credit_days;
                        } elseif ($value['transaction_detail'] == 'emails.channel_partner_active_to_sales_user' || $value['transaction_detail'] == 'emails.new_channel_partener_assign_to_sales_user' || $value['transaction_detail'] == 'emails.channel_partner_active_welcome_mail') {
                            $value['mobile_number'] = $User->phone_number;
                            $value['user_email'] = $User->email;
                            $value['city_name'] = getCityName($ChannelPartner->d_city_id);
                            $value['channel_partner_type'] = getChannelPartners()[$User->type]['short_name'];

                            $ChannelPartnerAsignSale_persons = User::select('first_name', 'last_name', 'email', 'phone_number')
                                ->where('id', $ChannelPartner->sale_persons)
                                ->first();
                            $value['sales_user_name'] = $ChannelPartnerAsignSale_persons->first_name . ' ' . $ChannelPartnerAsignSale_persons->last_name;
                            $value['sales_user_email'] = $ChannelPartnerAsignSale_persons->email;
                            $value['sales_user_mobile'] = $ChannelPartnerAsignSale_persons->phone_number;

                            $SalesUserReporting_manager = User::select('first_name', 'last_name', 'email', 'phone_number')
                                ->where('id', $ChannelPartner->sale_persons)
                                ->first();
                            $value['reporting_manager_name'] = $SalesUserReporting_manager->first_name . ' ' . $SalesUserReporting_manager->last_name;
                            $value['reporting_manager_mobile'] = $SalesUserReporting_manager->phone_number;
                            $value['reporting_manager_email'] = $SalesUserReporting_manager->email;

                            if ($value['remark'] == 'New Channel Partner Executive Assigned' || $value['transaction_detail'] == 'emails.new_channel_partener_assign_to_sales_user') {
                                // $value['to_email'] = $ChannelPartnerAsignSale_persons->email;
                                // $value['to_name'] = $ChannelPartnerAsignSale_persons->first_name . ' ' . $ChannelPartnerAsignSale_persons->last_name;
                            }
                        }
                    }

                    if ($value['transaction_name'] == 'Lead') {
                        $Lead = Lead::find($value['transaction_id']);
                        $objLeadOwner = User::where('id', $Lead['assigned_to'])->first();
                        $value['transaction_data'] = $Lead;
                        $CityList = CityList::select('city_list.id', 'city_list.name as city_list_name', 'state_list.name as state_list_name', 'country_list.name as country_list_name');
                        $CityList->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
                        $CityList->leftJoin('country_list', 'country_list.id', '=', 'city_list.country_id');
                        $CityList->where('city_list.id', $Lead['city_id']);
                        $CityList = $CityList->first();

                        $newCityList = array();
                        if($CityList){
                            $newCityList['id'] = $CityList['id'];
                            $newCityList['city_list_name'] = $CityList['city_list_name'];
                            $newCityList['state_list_name'] = $CityList['state_list_name'];
                            $newCityList['country_list_name'] = $CityList['country_list_name'];
                        }else{
                            $newCityList['id'] = '';
                            $newCityList['city_list_name'] = '';
                            $newCityList['state_list_name'] = '';
                            $newCityList['country_list_name'] = '';
                        }


                        $value['transaction_data']['city'] = $newCityList;
                        $value['transaction_data']['lead_owner'] = $objLeadOwner->first_name.' '.$objLeadOwner->last_name;

                        $source_type = explode("-", $Lead['source_type']);
                        $sourceType = '';
				        foreach (getLeadSourceTypes() as $skey => $svalue) {
				        	if ($svalue['type'] == $source_type[0] && $svalue['id'] == $source_type[1]) {
				        		$sourceType = $svalue['lable'];
				        	}
				        }
                        $source = '';
                        if($source_type[0] == 'user') {
                            if(in_array($source_type[1], array(101, 102, 103, 104, 105))) {
                                $sourceUser = ChannelPartner::select('firm_name')->where('user_id', $Lead['source'])->first();
                                if($sourceUser) {
                                    $source = $sourceUser['firm_name'] .' ' .$sourceType;
                                } else {
                                    $source = '';
                                }
                            } else {
                                $sourceUser = User::find($Lead['source']);
                                if($sourceUser) {
                                    $source = $sourceUser['first_name'] .' '. $sourceUser['last_name'];
                                } else {
                                    $source = '';
                                }
                            }
                        } else if($source_type[0] == 'exhibition') {
                            $sourceUser = Exhibition::find($Lead['source']);
                            if($sourceUser) {
                                $source = $sourceUser['name'] . ' ' .$sourceType;
                            } else {
                                $source = '';
                            }
                        } else {
                            $source = $Lead['source'];
                        }

                        $value['transaction_data']['source_type'] = $sourceType;
                        $value['transaction_data']['source'] = $source;

                        if ($Lead['is_deal'] == 1) {
                            $Quotation = Wltrn_Quotation::select('quot_total_amount')
                                ->where('inquiry_id', $value['transaction_id'])
                                ->where('isfinal', 1)
                                ->first();
                            if ($Quotation) {
                                $value['transaction_data']['quotation_amt'] = $Quotation->quot_total_amount;
                            } else {
                                $value['transaction_data']['quotation_amt'] = 0.0;
                            }
                        } else {
                            $value['transaction_data']['quotation_amt'] = 0.0;
                        }
                    }

                    if ($value['transaction_name'] == 'Deal') {
                        $Lead = Lead::find($value['lead_id']);
                        $User = User::where('id', $value['transaction_id'])->first();

                        $value['inquiry_id'] = $value['lead_id'];
                        $value['client_name'] = $Lead['first_name'] . ' ' . $Lead['last_name'];
                        $value['first_name'] = $User->first_name;
                        $value['last_name'] = $User->last_name;
                        $value['file_url'] = getSpaceFilePath($value['attachment']);

                        if($User->type == 202) {
                            $Architect = Architect::where('user_id', $User->id)->first();
                            $total_point = $Architect->total_point;
                        } else if($User->type == 302) {
                            $Electrician = Electrician::where('user_id', $User->id)->first();
                            $total_point = $Electrician->total_point;
                        }
                        $value['total_point'] = $total_point;
                    }

                    try {

                        if (Config::get('app.env') == 'local') {
                            $value['to_email'] = $configrationForNotify['test_email'];
                            $value['bcc_email'] = implode(', ', $configrationForNotify['test_email_bcc']);
                        }

                        if(isset($value['to_email']) && ($value['to_email'] != '' || $value['to_email'] != null)){
                            Mail::send($value['transaction_detail'], ['params' => $value], function ($m) use ($value) {
                                $m->from($value['from_mail'], $value['from_name']);
                                $m->bcc(explode(',', str_replace(' ', '', $value['bcc_mail'])));
                                $m->to(explode(',', str_replace(' ', '', $value['to_email'])), $value['to_name'])->subject($value['subject']);
                                if ($value['attachment'] != '') {
                                    if($value['transaction_name'] == 'Architect' || $value['transaction_name'] == 'Electrician') {
                                        $file_name = 'Smart Club Reward Program';	
                                        foreach (explode(',', $value['attachment']) as $helpDocument) {
                                            $fileName = preg_replace('![^a-z0-9]+!i', '-', $helpDocument);
                                            $fileExtension = explode('.', $helpDocument);
                                            $fileExtension = end($fileExtension);
                                            $fileName =  $file_name.'.'.$fileExtension;
                                            $m->attach(getSpaceFilePath($helpDocument), [
                                                'as' => $fileName,
                                            ]);
                                        }
                                    } 
        
                                    if ($value['transaction_name'] == 'Lead') {
                                        $Lead = Lead::find($value['transaction_id']);
                                        $file_name = $Lead['first_name'].''.$Lead['last_name'];
        
                                        $fileName = preg_replace('![^a-z0-9]+!i', '-',  $value['attachment']);
                                        $fileExtension = explode('.',  $value['attachment']);
                                        $fileExtension = end($fileExtension);
                                        $fileName =  $file_name.'.'.$fileExtension;
                                        $m->attach(getSpaceFilePath($value['attachment']), [
                                            'as' => $fileName,
                                        ]);
                                    }
        
                                    if ($value['transaction_name'] == 'Deal') {
                                        $Lead = Lead::find($value['lead_id']);
                                        $file_name = $Lead['first_name'].''.$Lead['last_name'];
        
                                        $fileName = preg_replace('![^a-z0-9]+!i', '-',  $value['attachment']);
                                        $fileExtension = explode('.',  $value['attachment']);
                                        $fileExtension = end($fileExtension);
                                        $fileName =  'Earned_Point.'.$fileExtension;
                                        $m->attach(getSpaceFilePath($value['attachment']), [
                                            'as' => $fileName,
                                        ]);
                                    }
                                }
                            });
                        }
                    } catch (\Throwable $th) {
                        throw $th;
                    }
                    

                    $UpdateStatus = NotificationScheduler::find($value['id']);
                    $UpdateStatus->status = 1;
                    $UpdateStatus->save();
                } elseif ($value['transaction_type'] == 'Whatsapp') {
                    $user_detail = User::where('id', $value['transaction_id'])->first();

                    if ($user_detail) {
                        $whatsapp_controller = new WhatsappApiContoller();
                        $perameater_request = new Request();
                        $perameater_request['q_whatsapp_massage_mobileno'] = $user_detail->phone_number;
                        $perameater_request['q_whatsapp_massage_template'] = $value['transaction_detail'];
                        $perameater_request['q_broadcast_name'] = $user_detail->first_name . ' ' . $user_detail->last_name;
					    $perameater_request['q_whatsapp_massage_attechment'] = getSpaceFilePath($value['attachment']);
                        $perameater_request['q_whatsapp_massage_parameters'] = [
                            [
                                'name' => 'name',
                                'value' => $user_detail->first_name . ' ' . $user_detail->last_name,
                            ],
                            [
                                'name' => 'username',
                                'value' => $user_detail->email,
                            ],
                            [
                                'name' => 'password',
                                'value' => '111111',
                            ],
                        ];
                        $wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
                    }

                    $UpdateStatus = NotificationScheduler::find($value['id']);
                    $UpdateStatus->status = 1;
                    $UpdateStatus->save();
                }
            }
        }
        return $NotificationScheduler;
    }
}

