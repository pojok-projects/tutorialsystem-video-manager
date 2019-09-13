<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class reactController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    private $client, $endpoint;

    public function __construct()
    {
        $this->client = new Client();
        $this->endpoint = env('ENDPOINT');
    }

    public function get($id, $attr)
    {
        $data = $this->client->request('GET', $this->endpoint . "/content/metadata/$id");
        if($data->getStatusCode() != 200) {
            return response()->json([
                'Message' => 'Bad Gateway'
            ], $data->getStatusCode());
        }
        $data = json_decode($data->getBody(), true)[$attr . 's'];
        $data = (is_null($data)) ? [] : $data;
        return $data;
    }

    public function res($request, $id, $attr)
    {
        
        $rules = [
            'user_id' => 'required'
        ];

        $message = [
            'required' => 'Please fill attribute :attribute'
        ];

        $this->validate($request, $rules, $message);

        $data = $this->get($id, $attr);

        $i = 0;
        foreach($data as $l) {
            if($l['user_id'] == $request->user_id) {
                unset($data[$i]);
                $result = $this->client->request('POST', $this->endpoint . "/content/metadata/update/$id", [
                    'form_params' => [
                        $attr . 's' => array_merge($data, [])
                    ]
                ]);
                if($result->getStatusCode() != 200) {
                    return response()->json([
                        'Message' => 'Bad Gateway'
                    ], $result->getStatusCode());
                }
                return response()->json([
                    'status' => [
                        'code' => 200,
                        'message' => 'video un' . $attr . 'd'
                    ],
                    $attr . 's' => array_merge($data, [])
                ]);
            }
            $i++;
        }
        $uuid = Str::uuid();
        $action = array([
                    'id' => $uuid,
                    'user_id' => $request->user_id,
                    'created_at' => date(DATE_ATOM),
                    'updated_at' => date(DATE_ATOM)
                ]);

        $result = $this->client->request('POST', $this->endpoint . "/content/metadata/update/$id", [
            'form_params' => [
                $attr . 's' => array_merge($data, $action)
            ]
        ]);
        if($result->getStatusCode() != 200) {
            return response()->json([
                'Message' => 'Bad Gateway'
            ], $result->getStatusCode());
        }

        $un = ($attr != 'like') ? 'like' : 'dislike';
        $un_data = $this->get($id, $un);
        $i = 0;
        foreach($un_data as $u) {
            if($u['user_id'] == $request->user_id) {
                unset($un_data[$i]);
                $result = $this->client->request('POST', $this->endpoint . "/content/metadata/update/$id", [
                    'form_params' => [
                        $un . 's' => array_merge($un_data, [])
                    ]
                ]);
                if($result->getStatusCode() != 200) {
                    return response()->json([
                        'Message' => 'Bad Gateway'
                    ], $result->getStatusCode());
                }
            }
            $i++;
        }

        return response()->json([
            'status' => [
                'code' => 200,
                'message' => 'video ' . $attr . 'd'
            ],
            $attr . 's' => array_merge($data, $action)
        ]);
    }

    public function like(Request $request, $id)
    {
        return $this->res($request, $id, 'like');
    }

    public function dislike(Request $request, $id)
    {  
        return $this->res($request, $id, 'dislike');
    }

    //
}
