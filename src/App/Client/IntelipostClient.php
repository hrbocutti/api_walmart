<?php
namespace App\Client;


class IntelipostClient
{
    protected $url, $method;

    function __construct($url, $method)
    {
        $this->url = $url;
        $this->method = $method;
    }

    public function call($json)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $this->method,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => array(
                "api_key: c11d12528468214a1962fa46751f5de4b8f2ad3ea9e99917fbec1a5f2f645af5",
                "platform: Walmart",
                "content-type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            http_response_code(400);
            echo json_encode(array("type"=>"ERROR", "Message"=>$err));
            die();
        }
        return $response;
    }
}