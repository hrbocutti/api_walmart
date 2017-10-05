<?php
namespace App\MVC\Controller;
use App\MVC\Model\OrderModel;
use App\MVC\View\OrderView;
use App\MVC\IController\IOrderController;

/**
 * Class OrderController
 * @package App\MVC\Controller
 */
class OrderController implements IOrderController
{
    public function create($order)
    {
        $order = json_decode($order);
        $model = new OrderModel();
        $pedidoId = $model->findOrder($order->marketplaceOrderId);
        if (!empty($pedidoId)){
            $view = new OrderView();
            $view->render($pedidoId, 200, "Success");
        }else{
            $idInserted = $model->persistOrder($order);
            if (!empty($idInserted)){
                $view = new OrderView();
                $view->render($idInserted, 200, "Success");
            }else{
                $view = new OrderView();
                $view->render($idInserted, 500, "Did not possible to create the order! ");
            }
        }
    }



    public function confirm($marketplaceOrderId)
    {
        // TODO: Implement confirm() method.
    }

    public function cancel($marketplaceOrderId)
    {
        // TODO: Implement cancel() method.
    }
}