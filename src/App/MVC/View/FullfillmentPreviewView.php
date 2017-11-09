<?php
/**
 * Created by PhpStorm.
 * User: T.I
 * Date: 05/10/2017
 * Time: 11:36
 */

namespace App\MVC\View;
use App\MVC\IView\IFullfillmentPreview;


class FullfillmentPreviewView implements IFullfillmentPreview
{

    public function render($json, $statusCode, $message)
    {
        header('Content-Type: application/json');
        header("HTTP/1.1 $statusCode $message");
        $message = json_encode(array("summary" => $message));
        echo (empty($json))?  $message:json_encode($json);
        die();
    }
}