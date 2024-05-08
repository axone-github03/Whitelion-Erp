<?php

namespace App\Http\Controllers\Ai;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

use GuzzleHttp\Client;

class AiChatContoller extends Controller
{
    public function getResponse(Request $request)
    {
        
        $inputText = $request->text;
        $data = [
            "prompt" => [
                "messages" => [
                    [
                        "content" => $inputText
                    ]
                ]
            ],
            "temperature" => 0.1,
            "candidateCount" => 1
        ];
        
        $PerameaterJson = json_encode($data);

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://generativelanguage.googleapis.com/v1beta2/models/chat-bison-001:generateMessage?key=AIzaSyDGYO2rzmZsl7WA4AOY_D8wbiZ08WV4t1Y',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$PerameaterJson,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);

        return json_decode($response)->candidates[0]->content;

    }
}