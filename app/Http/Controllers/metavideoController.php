<?php

namespace App\Http\Controllers;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class metavideoController extends Controller
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

    private function get_videos($id) {
        $result = $this->client->request('GET', $this->endpoint . "content/metadata/$id");

        if($result->getStatusCode() != 200) {
            return response()->json([
                'Message' => 'Bad Gateway'
            ], $result->getStatusCode());
        }

        $data = json_decode($result->getBody(), true)['metavideos'];
        return $data;
    }

    public function index($id)
    {
        $result = $this->get_videos($id);
        return response()->json($result, 200);
    }

    public function create(Request $request, $id)
    {
        $rules = [
            'file_name' => 'required',
            'duration' => 'required',
            'file_path' => 'required',
            'size' => 'required',
            'format' => 'required',
            'resolution' => 'required'
        ];
        $message = [
            'required' => 'Please fill attribute :attribute'
        ];

        $this->validate($request, $rules, $message);


        $uuid = (string) str::uuid();
        $video = array([
            'duration' => $request->duration,
            'file_path' => $request->file_path,
            'size' => $request->size,
            'updated_at' => date(DATE_ATOM),
            'file_name' => $request->file_name,
            'format' => $request->format,
            'created_at' => date(DATE_ATOM),
            'id' => $uuid,
            'resolution' => $request->resolution
        ]);
        
        $videos = $this->get_videos($id);
        $result = array_merge($videos, $video);
        
        $data = $this->client->request('POST', $this->endpoint . "content/metadata/update/$id", [
            'form_params' => [
                'metavideos' => $result
            ]
        ]);

        if($data->getStatusCode() != 200) {
            return response()->json([
                'Message' => 'Bad Gateway'
            ], $data->getStatusCode());
        }

        return response()->json([
            'status' => [
                'code' => 200,
                'message' => 'upload success'
            ],
            'result' => [
                'video_id' => $uuid
            ]
        ]);
    }

    public function update(Request $request)
    {
        $rules = [
            'id' => 'required',
            'metavideo_id' => 'required'
        ];

        $message = [
            'required' => 'Please fill attribute :attribute'
        ];

        $this->validate($request, $rules, $message);

        $videos = $this->get_videos($request->id);

        $i = 0;
        $update = null;
        foreach($videos as $s) {
            if($s['id'] == $request->metavideo_id) {
                $update = $s;
                unset($videos[$i]);
            }
            $i++;
        }

        if(is_null($update)) {
            return response()->json([
                'status' => [
                    'code' => 404,
                    'message' => 'video not found'
                ]
                ], 404);
        }
        
        $file_path = (isset($request->file_path)) ? $request->file_path : $update['file_path'];
        $duration = (isset($request->duration)) ? $request->duration : $update['duration'];
        $size = (isset($request->size)) ? $request->size : $update['size'];
        $file_name = (isset($request->file_name)) ? $request->file_name : $update['file_name'];
        $format = (isset($request->format)) ? $request->format : $update['format'];
        $resolution = (isset($request->resolution)) ? $request->resolution : $update['resolution'];

        $video = [
            'duration' => $duration,
            'file_path' => $file_path,
            'size' => $size,
            'updated_at' => date(DATE_ATOM),
            'file_name' => $file_name,
            'format' => $format,
            'created_at' => $update['created_at'],
            'id' => $update['id'],
            'resolution' => $resolution
        ];

        $result = $this->client->request('POST', $this->endpoint . "content/metadata/update/$request->id", [
            'form_params' => [
                'metavideos' => array_merge($videos, $video)
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
                'message' => 'Update Success'
            ],
            'result' => $video
        ], 200);

    }

    public function destroy(Request $request)
    {
        $rules = [
            'id' => 'required',
            'metavideo_id' => 'required'
        ];

        $message = [
            'required' => 'Please fill attribute :attribute'
        ];

        $this->validate($request, $rules, $message);

        $videos = $this->get_videos($request->id);

        $status = null;
        $i = 0;
        foreach($videos as $s) {
            if($s['id'] == $request->metavideo_id) {
                unset($videos[$i]);
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

        $result = $this->client->request('POST', $this->endpoint . "content/metadata/update/$request->id", [
            'form_params' => [
                'metavideos' => $videos
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
