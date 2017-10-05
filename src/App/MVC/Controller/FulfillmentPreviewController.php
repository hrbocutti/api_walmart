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

        if(empty($quotation)){
            $msg = 'Product not found';
            $view->render(null, 500, $msg);
        }

        $view->render($quotation, 200, null);
    }
}