<?php
namespace App\MVC\Controller;
use App\MVC\Model\FulfillmentPreviewModel;
use App\MVC\View\FullfillmentPreviewView;

class FulfillmentPreviewController
{
    public function index($request)
    {
        $model = new FulfillmentPreviewModel();
        $quotation = $model->cotacao($request);
        $view = new FullfillmentPreviewView();
        $view->render($quotation, 200, null);
    }
}