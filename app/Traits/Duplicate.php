<?php

namespace App\Traits;
use GuzzleHttp\Client;

/**
 * 
 */
trait Duplicate
{
    private $endpoint, $client;

    public function __construct() {
        $this->client = new Client();
        $this->endpoint = env('ENDPOINT');
    }

    public function cekDuplicate($query)
    {
        $result = $this->client->request('POST', $this->endpoint . 'content/metadata/search', [
            'form_params' => [
                'query' => urlencode($query)
            ]
        ]);

        return $this->response_data($result);
    }
    
    private function response_data($result) {
        if($result->getStatusCode() != 200) {
            return response()->json([
                'Message' => 'Bad Gateway',
                'code' => 502
            ], $result->getStatusCode());
        }
    
        return response()->json(json_decode($result->getBody()), $result->getStatusCode());
    }

    private function get_videos($id) {
        $result = $this->client->request('GET', $this->endpoint . "content/metadata/$id");

        if($result->getStatusCode() != 200) {
            if($result->getStatusCode() != 200) {
                return response()->json([
                    'Message' => 'Bad Gateway'
                ], $result->getStatusCode());
            }
        }

        $data = json_decode($result->getBody(), true)['metavideos'];
        return $data;
    }
}
