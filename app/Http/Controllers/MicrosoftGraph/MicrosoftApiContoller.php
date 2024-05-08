<?php

namespace App\Http\Controllers\MicrosoftGraph;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\Log;
use Exception;

class MicrosoftApiContoller extends Controller
{
    public function getAccessToken()
    {
        $tokenEndpoint = "https://login.microsoftonline.com/" . env('MS_TENANT_ID') . "/oauth2/token";
        $client = new Client();
        $response = $client->post($tokenEndpoint, [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => env('MS_CLIENT_ID'),
                'client_secret' => env('MS_CLIENT_SECRET'),
                'resource' => env('MS_RESOURCE'),
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getUserDetail(Request $request)
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->getAccessToken()['access_token'],
            'Accept' => 'application/json',
        ];

        $client = new Client();
        $response = $client->get(env('MS_BASE_URL') . $request->user_mail, [
            'headers' => $headers,
        ]);

        $userData = json_decode($response->getBody(), true);
    }

    public function createClanderEvent(Request $request)
    {

        $rules = array();
        $rules['main_mail'] = 'required|email';
        $rules['title'] = 'required';
        $rules['location'] = 'required';
        $rules['start_datetime'] = 'required';
        $rules['end_datetime'] = 'required';


        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

            $response = errorRes($validator->errors()->first());
        } else {

            $main_mail = $request->main_mail;
            $subject = $request->title;
            $bodyContentType = "HTML";
            $bodyContent = $request->notes . ' At ' . $request->location;
            $startDateTime = $request->start_datetime;
            //  date('Y-m-d H:i:s', strtotime($dateTime . " +1 hours")); TEMPORY USE END TIME AFTER 1 HOURS OF START TIME
            $endDateTime = $request->end_datetime;
            $attendees = $request->attendees;
            $timeZone = "UTC";
            $reminderMinutes = $request->reminder_minute; // Set Reminder Using Minutes If Set Reminder Day Then (24 * day * 60)

            $headers = [
                'Authorization' => 'Bearer ' . $this->getAccessToken()['access_token'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            $calendarEvent = [
                "subject" => $subject,
                "body" => [
                    "contentType" => $bodyContentType,
                    "content" => $bodyContent
                ],
                "start" => [
                    "dateTime" => $startDateTime,
                    "timeZone" => $timeZone
                ],
                "end" => [
                    "dateTime" => $endDateTime,
                    "timeZone" => $timeZone
                ],
                "attendees" => $attendees,
                // "isOnlineMeeting" => true,
                // "onlineMeetingProvider" => "teamsForBusiness"
                "reminderMinutesBeforeStart" => $reminderMinutes
            ];

            $client = new Client();
            $response = $client->post(env('MS_BASE_URL'). 'users/' . $main_mail . '/events', [
                'headers' => $headers,
                'json' => $calendarEvent,
            ]);

            $response = $response->getBody();
            // $response['perameater_headers'] = $headers;
            // $response['perameater_json'] = $calendarEvent;

        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function findMeetingTimes(Request $request)
    {
        try {
            $rules = array();
            $rules['main_mail'] = 'required|email';
            $rules['start_datetime'] = 'required';
            $rules['end_datetime'] = 'required';
            $rules['schedules'] = 'required';
            $rules['interval_minute'] = 'required';

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {

                $response = errorRes($validator->errors()->first());
            } else {

                $main_mail = $request->main_mail;
                $startDateTime = $request->start_datetime;
                $endDateTime = $request->end_datetime;
                $timeZone = "UTC";
                $reminderMinutes = $request->reminder_minute; // Set Reminder Using Minutes If Set Reminder Day Then (24 * day * 60)

                $headers = [
                    'Authorization' => 'Bearer ' . $this->getAccessToken()['access_token'],
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ];

                $calendarEvent = [
                    "schedules" => $request->schedules,
                    "startTime" => [
                        "dateTime" => $startDateTime,
                        "timeZone" => $timeZone
                    ],
                    "endTime" => [
                        "dateTime" => $endDateTime,
                        "timeZone" => $timeZone
                    ],
                    "reminderMinutesBeforeStart" => $request->interval_minute
                ];

                $client = new Client();
                $response = $client->post(env('MS_BASE_URL'). 'users/' . $main_mail . '/calendar/getSchedule', [
                    'headers' => $headers,
                    'json' => $calendarEvent,
                ]);

            }
            $response =  json_decode($response->getBody(), true);
            $response['sc'] =  $calendarEvent;
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $reasonPhrase = $response->getReasonPhrase();
            $fullError = $response->getBody()->getContents();
            $response = $fullError;    
        }
        return $response;

    }
}