<?php

namespace App\Http\Controllers\Whatsapp;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class WhatsappApiContoller extends Controller
{
    public function getMessageTemplate(Request $request)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://live-server-103607.wati.io/api/v1/getMessageTemplates',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => ['Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiIzM2E2MWYyZC1hZmIxLTQyMGYtYWVjZC02ODcwZjRlYzMwODYiLCJ1bmlxdWVfbmFtZSI6ImFua2l0LmluMTE4NEBnbWFpbC5jb20iLCJuYW1laWQiOiJhbmtpdC5pbjExODRAZ21haWwuY29tIiwiZW1haWwiOiJhbmtpdC5pbjExODRAZ21haWwuY29tIiwiYXV0aF90aW1lIjoiMDMvMTQvMjAyMyAwNToyODozNCIsImRiX25hbWUiOiIxMDM2MDciLCJodHRwOi8vc2NoZW1hcy5taWNyb3NvZnQuY29tL3dzLzIwMDgvMDYvaWRlbnRpdHkvY2xhaW1zL3JvbGUiOiJBRE1JTklTVFJBVE9SIiwiZXhwIjoyNTM0MDIzMDA4MDAsImlzcyI6IkNsYXJlX0FJIiwiYXVkIjoiQ2xhcmVfQUkifQ.jY183d74h21Vp__STwlNIocxRLLceRSAtsljly_fS1I', 'Cookie: affinity=1678877442.691.200912.393887|60582e1a1417c00ce6f9b2b83948e1d1'],
        ]);

        $templateList = curl_exec($curl);
        curl_close($curl);

        $templateList = json_decode($templateList);

        if ($templateList->result == 'success') {
            $templatedata = $templateList->messageTemplates;

            $templateselectdata = [];
            foreach ($templatedata as $value) {
                if ($value->status == 'APPROVED') {
                    $templateselectdata[] = ['id' => $value->elementName, 'text' => $value->elementName];
                }
            }

            $response = [];
            $response = successRes('Template List Get Successfull');
            $response['results'] = $templateselectdata;
            $response['pagination']['more'] = true;
        } else {
            $response = errorRes('Please Contact To Admin');
        }

        return response()->json($response)->header('Content-Type', 'application/json');
    }
    // public function getplanet(Request $request)
    // {
    //     $curl = curl_init();

    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => 'https://auth.qa-tax.planetpayment.ae/auth/realms/planet/protocol/openid-connect/token',
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS => 'client_id=2ae6f4c8-07fb-497d-adff-1bcfa0da6773&client_secret=ieexuMNDD2HOYPtTNXya9STsXEErpTxt&grant_type=client_credentials',
    //         CURLOPT_HTTPHEADER => array(
    //             'Content-Type: application/x-www-form-urlencoded'
    //         ),
    //     )
    //     );

    //     $response = curl_exec($curl);

    //     curl_close($curl);

    //     return response()->json($response)->header('Content-Type', 'application/json');
    // }

    public function sendTemplateMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q_whatsapp_massage_mobileno' => ['required'],
            'q_whatsapp_massage_template' => ['required'],
            'q_broadcast_name' => ['required'],
        ]);

        if ($validator->fails()) {
            $response = [];
            $response['status'] = 0;
            $response['msg'] = 'The request could not be understood by the server due to malformed syntax';
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $curl = curl_init();

            $name = $request->q_broadcast_name;
            $template_name = $request->q_whatsapp_massage_template;
            $attechment = $request->q_whatsapp_massage_attechment;
            $parameters = $request->q_whatsapp_massage_parameters;

            $mobileNO = $request->q_whatsapp_massage_mobileno;
            $configrationForNotify = configrationForNotify();
            if (Config::get('app.env') == 'local') {
                // SEND MAIL
                $mobileNO = $configrationForNotify['test_phone_number'];
            }
            $postfield = [
                'authToken' => 'U2FsdGVkX19Rk6DBRG48Vb4GeKho/YXR8iF52TuS7lH9XX+QYLCzySb+ryQMmmArfd1aaDh2E4d4U2TqlQATtrjiASQBaC4fMUL5GsNwR7OtOoppUwEzDJw3RzEA8cnlLcAc6u1/cBHLrrUJJfE5Jl7DgGl9Dr2qZ+dKzFepfbkUUv9SI3I/HDlxJuDCnUBx',
                'name' => $name,
                'sendto' => '91'.$mobileNO,
                'originWebsite' => 'https://www.whitelion.in/',
                'templateName' => $template_name,
                'language' => 'en_US',
                'myfile' => $attechment,
                'buttonValue' => '',
                'isTinyURL' => '',
                'headerdata[0]' => 'headerdata',
                'tags' => '',
            ];
            $postfield = array_merge($postfield,$parameters);

            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://app.11za.in/apis/template/sendTemplate',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $postfield,
            ]);

            $res_send_message = curl_exec($curl);

            curl_close($curl);
            $res_send_message = json_decode($res_send_message);
            if ($res_send_message->IsSuccess == true) {
                $response = successRes('Whatsapp Message Sent Successfull');
                $response['data'] = $postfield;
            } else {
                $response = errorRes('Please Contact To Admin');
                $response['data'] = $res_send_message->Message;
                $response['postfield'] = $postfield;
            }
            $response['data_response'] = $res_send_message;
        }
        // $response = successRes('Whatsapp Message Sent Successfull');
        return response()->json($response)->header('Content-Type', 'application/json');
    }
    public function sendTemplateMessageWatti(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q_whatsapp_massage_mobileno' => ['required'],
            'q_whatsapp_massage_template' => ['required'],
            'q_broadcast_name' => ['required'],
        ]);

        if ($validator->fails()) {
            $response = [];
            $response['status'] = 0;
            $response['msg'] = 'The request could not be understood by the server due to malformed syntax';
            $response['statuscode'] = 400;
            $response['data'] = $validator->errors();

            return response()->json($response)->header('Content-Type', 'application/json');
        } else {
            $curl = curl_init();

            if (isset($request->q_whatsapp_massage_parameters)) {
                $postfield = [
                    'template_name' => $request->q_whatsapp_massage_template,
                    'broadcast_name' => $request->q_broadcast_name,
                    'parameters' => $request->q_whatsapp_massage_parameters,
                ];
            } else {
                $postfield = [
                    'template_name' => $request->q_whatsapp_massage_template,
                    'broadcast_name' => $request->q_broadcast_name,
                    'parameters' => [
                        [
                            'name' => 'name',
                            'value' => $request->q_broadcast_name,
                        ],
                    ],
                ];
            }
            $mobileNO = $request->q_whatsapp_massage_mobileno;
            $configrationForNotify = configrationForNotify();
            if (Config::get('app.env') == 'local') {
                // SEND MAIL
                $mobileNO = $configrationForNotify['test_phone_number'];
            }
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://live-server-103607.wati.io/api/v1/sendTemplateMessage?whatsappNumber=' . $mobileNO,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($postfield),
                CURLOPT_HTTPHEADER => ['Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiIzM2E2MWYyZC1hZmIxLTQyMGYtYWVjZC02ODcwZjRlYzMwODYiLCJ1bmlxdWVfbmFtZSI6ImFua2l0LmluMTE4NEBnbWFpbC5jb20iLCJuYW1laWQiOiJhbmtpdC5pbjExODRAZ21haWwuY29tIiwiZW1haWwiOiJhbmtpdC5pbjExODRAZ21haWwuY29tIiwiYXV0aF90aW1lIjoiMDMvMTQvMjAyMyAwNToyODozNCIsImRiX25hbWUiOiIxMDM2MDciLCJodHRwOi8vc2NoZW1hcy5taWNyb3NvZnQuY29tL3dzLzIwMDgvMDYvaWRlbnRpdHkvY2xhaW1zL3JvbGUiOiJBRE1JTklTVFJBVE9SIiwiZXhwIjoyNTM0MDIzMDA4MDAsImlzcyI6IkNsYXJlX0FJIiwiYXVkIjoiQ2xhcmVfQUkifQ.jY183d74h21Vp__STwlNIocxRLLceRSAtsljly_fS1I', 'Content-Type: application/json', 'Cookie: affinity=1678941179.214.196049.405042|60582e1a1417c00ce6f9b2b83948e1d1'],
            ]);

            $res_send_message = curl_exec($curl);

            curl_close($curl);
            $res_send_message = json_decode($res_send_message);
            if ($res_send_message->result == true) {
                $response = successRes('Whatsapp Message Sent Successfull');
            } else {
                $response = errorRes('Please Contact To Admin');
                $response['data'] = $res_send_message->info;
            }
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function sendtest(Request $request)
    {
        $whatsapp_controller = new WhatsappApiContoller();
        $perameater_request = new Request();
        $perameater_request['q_whatsapp_massage_mobileno'] = '919824717656';
		$perameater_request['q_whatsapp_massage_template'] = 'architect_request_to_claim_gift';
		$perameater_request['q_whatsapp_massage_attechment'] = '';
		$perameater_request['q_broadcast_name'] = 'ankit';
		$perameater_request['q_whatsapp_massage_parameters'] = array(
            'data[0]' => 'fgff',
            'data[1]' => '56464',
            'data[2]' => '52',
            'data[3]' => 'shdah',
            'data[4]' => 'Namrata Bhawagar'
        );
        $wp_response = $whatsapp_controller->sendTemplateMessage($perameater_request);
        $response['whatsapp'] = $wp_response;
        return $response;
    }


}
