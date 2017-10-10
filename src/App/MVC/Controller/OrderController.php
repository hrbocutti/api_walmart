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
        if (empty($order)){
            header("HTTP/1.1 400", null, 400);
            echo json_encode(array("summary" => "Invalid body"));
            die();
        }
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



    public function confirm($marketplaceOrderId, $body)
    {
       $model = new OrderModel();
       $orderId = $model->confirmPayment($marketplaceOrderId, $body);
       $view = new OrderView();
       if(!empty($orderId)){
           $view->render($orderId, 200, null);
       }else{
           $view->render(null, 500, "Couldn't confirm the payment, try again !");
       }

    }

    public function cancel($marketplaceOrderId, $body)
    {
        $model = new OrderModel();
        $orderId = $model->cancelOrder($marketplaceOrderId, $body);
        $view = new OrderView();

        if(!empty($orderId)){
            $view->render($orderId, 200, null);

        }else{
            $view->render(null, 500, "Couldn't cancel order, try again !");
        }
    }
}