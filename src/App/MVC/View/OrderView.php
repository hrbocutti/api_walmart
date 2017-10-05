<?php
namespace App\MVC\View;
use \Psr\Http\Message\ResponseInterface as Response;


class OrderView
{

    public function render($json, $statusCode, $message)
    {
        header("HTTP/1.1 $statusCode $message");
        $message = json_encode(array("summary" => $message));
        echo (empty($json))?  $message:json_encode($json);
        die();
    }

}