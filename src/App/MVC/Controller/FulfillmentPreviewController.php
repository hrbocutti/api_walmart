<?php
namespace App\MVC\Controller;
use App\MVC\Model\FulfillmentPreviewModel;

class FulfillmentPreviewController
{
    public function index($request)
    {
        $model = new FulfillmentPreviewModel();
        $model->cotacao($request);
    }
}