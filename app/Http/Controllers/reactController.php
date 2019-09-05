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

    public function like(Request $request, $id)
    {
        
        $rules = [
            'user_id' => 'required'
        ];

        $message = [
            'required' => 'Please fill attribute :attribute'
        ];

        $this->validate($request, $rules, $message);

        $likes = $this->client->request('GET', $this->endpoint . "/content/metadata/$id");
        if($likes->getStatusCode() != 200) {
            return response()->json([
                'Message' => 'Bad Gateway'
            ], $likes->getStatusCode());
        }
        $likes = json_decode($likes->getBody(), true)['likes'];
        $likes = (is_null($likes['likes'])) ? [] : $likes['likes'];

        $i = 0;
        foreach($likes as $l) {
            if($l['user_id'] == $request->user_id) {
                unset($likes[$i]);
                $result = $this->client->request('POST', $this->endpoint . "/content/metadata/update/$id", [
                    'form_params' => [
                        'likes' => $likes
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
                        'message' => 'video unliked'
                    ],
                    'result' => array_merge($likes, [])
                ]);
            }
            $i++;
        }
        $uuid = Str::uuid();
        $like = array([
                    'id' => $uuid,
                    'user_id' => $request->user_id,
                    'created_at' => date(DATE_ATOM),
                    'updated_at' => date(DATE_ATOM)
                ]);

        $result = $this->client->request('POST', $this->endpoint . "/content/metadata/update/$id", [
            'form_params' => [
                'likes' => array_merge($likes, $like)
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
                'message' => 'video liked'
            ],
            'result' => array_merge($likes, $like)
        ]);
    }


    public function dislike(Request $request, $id)
    {  
        $rules = [
            'user_id' => 'required'
        ];

        $message = [
            'required' => 'Please fill attribute :attribute'
        ];

        $this->validate($request, $rules, $message);

        $dislikes = $this->client->request('GET', $this->endpoint . "/content/metadata/$id");
        if($dislikes->getStatusCode() != 200) {
            return response()->json([
                'Message' => 'Bad Gateway'
            ], $dislikes->getStatusCode());
        }
        $dislikes = json_decode($dislikes->getBody(), true)['dislikes'];
        $dislikes = (is_null($dislikes['dislikes'])) ? [] : $dislikes['dislikes'];

        $i = 0;
        foreach($dislikes as $l) {
            if($l['user_id'] == $request->user_id) {
                unset($dislikes[$i]);
                $result = $this->client->request('POST', $this->endpoint . "/content/metadata/update/$id", [
                    'form_params' => [
                        'dislikes' => array_merge($dislikes, [])
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
                        'message' => 'video undisliked'
                    ],
                    'result' => array_merge($dislikes, [])
                ]);
            }
            $i++;
        }
        $uuid = Str::uuid();
        $dislike = array([
                    'id' => $uuid,
                    'user_id' => $request->user_id,
                    'created_at' => date(DATE_ATOM),
                    'updated_at' => date(DATE_ATOM)
                ]);

        $result = $this->client->request('POST', $this->endpoint . "/content/metadata/update/$id", [
            'form_params' => [
                'dislikes' => array_merge($dislikes, $dislike)
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
                'message' => 'video disliked'
            ],
            'result' => array_merge($dislikes, $dislike)
        ]);
    }

    //
}
