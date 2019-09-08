<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class subtitleController extends Controller
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

    public function index($id)
    {
        $result = $this->client->request('GET', $this->endpoint . "content/metadata/$id");
        if($result->getStatusCode() != 200) {
            return response()->json([
                'Message' => 'Bad Gateway'
            ], $result->getStatusCode());
        }
        $subtitle = json_decode($result->getStatusCode(), true);
        $subtitle = $subtitle['subtitle'];
        return response()->json($subtitle, 200);
    }

    public function create(Request $request, $id)
    {
        $rules = [
            "file_path" => 'required',
            'subtitle_category_id' => 'required'
        ];
        $message = [
            'required' => 'Please fill attribute :attribute'
        ];

        $this->validate($request, $rules, $message);

        $subtitle = $this->client->request('GET', $this->endpoint . "content/metadata/$id");
        if($subtitle->getStatusCode() != 200) {
            return response()->json([
                'Message' => 'Bad Gateway'
            ], $subtitle->getStatusCode());
        }
        $subtitle = json_decode($subtitle->getBody(), true);
        $subtitle = (is_null($subtitle['subtitle'])) ? [] : $subtitle['subtitle'];

        $uuid = (string) str::uuid();
        $sub = array([
            'id' => $uuid,
            'subtitle_category_id' => $request->subtitle_category_id,
            'file_path' => $request->file_path,
            'created_at' => date(DATE_ATOM),
            'updated_at' => date(DATE_ATOM)
        ]);

        $i = 0;
        foreach($subtitle as $s) {
            if($s['id'] == $request->subtitle_category_id) {
                return response()->json([
                    'status' => [
                        'code' => 409,
                        'message' => 'subtitle with this subtitle_category_id is alrady exists!'
                    ],
                    'result' => $s
                ]);
                $i++;
            }
        }
        

        $result = $this->client->request('POST', $this->endpoint . "content/metadata/update/$id", [
            'form_params' => [
                'subtitle' => array_merge($subtitle, $sub)
            ]
        ]);

        if($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'Bad Gateway'
                ],
            ], $result->getStatusCode());
        }

        return response()->json([
            'status' => [
                'code' => 200,
                'message' => 'Subtitle Created!'
            ],
            'result' => [
                'subtitle_id' => $uuid
            ]
        ]);
    }

    public function update(Request $request)
    {
        $rules = [
            'metadata_id' => 'required',
            'id' => 'required'
        ];

        $message = [
            'required' => 'Please fill attribute :attribute'
        ];

        $this->validate($request, $rules, $message);

        $subtitle = $this->client->request('GET', $this->endpoint . "content/metadata/$request->metadata_id");
        if($subtitle->getStatusCode() != 200) {
            return response()->json([
                'Message' => 'Bad Gateway'
            ], $subtitle->getStatusCode());
        }
        $subtitle = json_decode($subtitle->getBody(), true);
        $subtitle = (is_null($subtitle['subtitle'])) ? [] : $subtitle['subtitle'];
        $i = 0;
        $update = null;
        foreach($subtitle as $s) {
            if($s['id'] == $request->id) {
                $update = $s;
                if(isset($request->subtitle_category_id)) {
                    if($s['subtitle_category_id'] == $request->subtitle_category_id) {
                        return response()->json([
                            'status' => [
                                'code' => 409,
                                'message' => 'subtitle with this subtitle_category_id is alrady exists!'
                            ],
                            'result' => $s
                        ]);
                    }
                }
                unset($subtitle[$i]);
            }
            $i++;
        }

        if(is_null($update)) {
            return response()->json([
                'status' => [
                    'code' => 404,
                    'message' => 'subtitle not found'
                ]
                ], 404);
        }

        $subtitle_category_id = (isset($request->subtitle_category_id)) ? $request->subtitle_category_id : $update['subtitle_category_id'];
        $file_path = (isset($request->file_path)) ? $request->file_path : $update['file_path'];

        $sub = [
            'id' => $update['id'],
            'subtitle_category_id' => $subtitle_category_id,
            'file_path' => $file_path,
            'created_at' => $update['created_at'],
            'updated_at' => date(DATE_ATOM)
        ];

        $result = $this->client->request('POST', $this->endpoint . "content/metadata/update/$request->metadata_id", [
            'form_params' => [
                'subtitle' => array_merge($subtitle, $sub)
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
                'message' => 'Subtitle Updated!'
            ],
            'result' => $sub
        ]);

    }

    public function destroy(Request $request)
    {
        $rules = [
            'id' => 'required',
            'metadata_id' => 'required'
        ];

        $message = [
            'required' => 'Please fill attribute :attribute'
        ];

        $this->validate($request, $rules, $message);

        $subtitle = $this->client->request('GET', $this->endpoint . "content/metadata/$request->metadata_id");
        if($subtitle->getStatusCode() != 200) {
            return response()->json([
                'Message' => 'Bad Gateway'
            ], $subtitle->getStatusCode());
        }
        $subtitle = json_decode($subtitle->getBody(), true);
        $subtitle = (is_null($subtitle['subtitle'])) ? [] : $subtitle['subtitle'];

        $status = null;
        $i = 0;
        foreach($subtitle as $s) {
            if($s['id'] == $request->id) {
                unset($subtitle[$i]);
                $status = 1;
            }
            $i++;
        }

        if(is_null($status)) {
            return response()->json([
                'status' => [
                    'code' => 404,
                    'message' => 'video not found'
                ]
            ]);
        }

        $result = $this->client->request('POST', $this->endpoint . "content/metadata/update/$request->metadata_id", [
            'form_params' => [
                'subtitle' => $subtitle
            ]
        ]);

        if($result->getStatusCode() != 200) {
            return response()->json([
                'status' => [
                    'code' => $result->getStatusCode(),
                    'message' => 'Bad Gateway'
                ]
                ], $result->getStatusCode());
        }

        return response()->json([
            'status' => [
                'code' => 200,
                'message' => 'Delete Success'
            ],
            'sub' => $subtitle
        ]);
    }

    //
}
