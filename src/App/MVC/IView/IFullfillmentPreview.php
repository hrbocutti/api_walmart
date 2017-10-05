<?php
/**
 * Created by PhpStorm.
 * User: T.I
 * Date: 05/10/2017
 * Time: 11:37
 */

namespace App\MVC\IView;


interface IFullfillmentPreview
{
    public function render($json, $statusCode, $message);
}