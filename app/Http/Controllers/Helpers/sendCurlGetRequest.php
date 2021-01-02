<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Controller;
use Session;

class sendCurlGetRequest extends Controller
{
    public $alerts = [
        'response' => [],
        'status' => false,
    ];

    public $requests = 0;

    public $cookies = [];

    public function __construct(array $request)
    {
        $this->init($request);
    }

    public function init(array $request)
    {
        $this->requests += 1;

        $data = [
            'params' => $request['params'],
            'url'     => $request['url'],
            'headers' => $request['headers']
        ];

        $url = $data['url'];
        $params = $data['params'] ?? [];

        $cookie = [];
        $headers = [];

        foreach ($data['headers'] as $key => $value) {
            $headers[] = "$key: $value";
        }

        if (!empty($params)) {
            $x = 0;
            $sub = '';
            $para = '';
            foreach ($params as $cc => $c) {
                if (count($params) - 1 > $x) {
                    $sub = '&';
                } else {
                    $sub = '';
                }
                $val = is_array($c) ? json_encode($c) : $c;
                $para .= $cc . '=' . str_replace([' '], ['%20'], $val . $sub);
                $x++;
            }
            $url = $url . '?' . $para;
        }

        $this->cookies[] = $cookie;

        // dd($content);
        // $content = '{"port":[1,2,3],"command":"send","text":"*125#"}';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_TIMEOUT, 62);

        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $all = curl_getinfo($curl);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $code = $all['http_code'];
        $response = [
            'links' => [],
            'status'  => $code,
            'response'  => $body
        ];
        $response['links'][] = $url;
        $response['success'] = true;
        $response['file'] = __FILE__;

        $this->alerts['status'] = true;
        $this->alerts['response'] = $response;

        return $response;
    }

    public function response()
    {
        return $this->alerts['response'];
    }
}
