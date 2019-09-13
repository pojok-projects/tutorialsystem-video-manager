<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class commentController extends Controller
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
        $result = $this->client->request('GET', $this->endpoint . "/content/metadata/$id");
        if($result->getStatusCode() != 200) {
            return response()->json([
                'Message' => 'Bad Gateway'
            ], $result->getStatusCode());
        }
        $comments = json_decode($result->getStatusCode(), true)['comments'];
        return response()->json($comments, 200);
    }

    public function create(Request $request, $metadata_id)
    {
        $rules = [
            'user_id' => 'required',
            'message' => 'required'
        ];

        $message = [
            'required' => 'Please fill attribute :attribute'
        ];

        $this->validate($request, $rules, $message);

        $comments = $this->client->request('GET', $this->endpoint . "/content/metadata/$metadata_id");
        if($comments->getStatusCode() != 200) {
            return response()->json([
                'Message' => 'Bad Gateway'
            ], $comments->getStatusCode());
        }
        $comments = json_decode($comments->getBody(), true);
        $comments = (is_null($comments['comments'])) ? [] : $comments['comments'];


        $uuid = (string) str::uuid();
        $comment = array([
            'id' => $uuid,
            'user_id' => $request->user_id,
            'reply_id' => [],
            'message' => $request->message,
            'creted_at' => date(DATE_ATOM),
            'updated_at' => date(DATE_ATOM)
        ]);

        $result = $this->client->request('POST', $this->endpoint . "/content/metadata/update/$metadata_id", [
            'form_params' => [
                'comments' => array_merge($comments, $comment)
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
                'message' => 'comment added'
            ],
            'result' => [
                'comment_id' => $uuid
            ]
        ]);
    }

    public function update(Request $request)
    {
        $rules = [
            'id' => 'required',
            'metadata_id' => 'required'
        ];

        $message = [
            'required' => 'Please fill attribute :attribute'
        ];

        $this->validate($request, $rules, $message);

        $metadata = $request->metadata_id;
        $comments = $this->client->request('GET', $this->endpoint . "/content/metadata/$metadata");
        if($comments->getStatusCode() != 200) {
            return response()->json([
                'Message' => 'Bad Gateway'
            ], $comments->getStatusCode());
        }
        $comments = json_decode($comments->getBody(), true);
        $comments = (is_null($comments['comments'])) ? [] : $comments['comments'];;

        $i = 0;
        $update = null;
        foreach($comments as $c) {
            if($c['id'] == $request->id) {
                $update = $c;
                unset($comments[$i]);
            }
            $i++;
        }

        if(is_null($update)) {
            return response()->json([
                'status' => [
                    'code' => 404,
                    'message' => 'comment not found'
                ]
                ], 404);
        }
        
        $message = (isset($request->message)) ? $request->message : $update['message'];
        
        $comment = array([
            'id' => $update['id'],
            'user_id' => $update['user_id'],
            'reply_id' => $update['reply_id'],
            'message' => $message,
            'created_at' => $update['created_at'],
            'updated_at' => date(DATE_ATOM)
        ]);

        $result = $this->client->request('POST', $this->endpoint . "/content/metadata/update/$request->metadata_id", [
            'form_params' => [
                'comments' => array_merge($comments, $comment)
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
                'message' => 'Comment Updated!'
            ],
            'result' => $comment
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

        $comments = $this->client->request('GET', $this->endpoint . "/content/metadata/$request->metadata_id");
        if($comments->getStatusCode() != 200) {
            return response()->json([
                'Message' => 'Bad Gateway'
            ], $comments->getStatusCode());
        }
        $comments = json_decode($comments->getBody(), true);
        $comments = (is_null($comments['comments'])) ? [] : $comments['comments'];

        $status = null;
        $i = 0;
        foreach($comments as $s) {
            if($s['id'] == $request->id) {
                unset($comments[$i]);
                $status = 1;
            }
            $i++;
        }

        if(is_null($status)) {
            return response()->json([
                'status' => [
                    'code' => 404,
                    'message' => 'comment not found'
                ]
            ]);
        }

        $result = $this->client->request('POST', $this->endpoint . "/content/metadata/update/$request->metadata_id", [
            'form_params' => [
                'comments' => array_merge($comments, [])
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
            ]
        ]);
    }


    //
}
