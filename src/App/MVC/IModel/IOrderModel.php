<?php
namespace App\MVC\IModel;


interface IOrderModel
{
    public function findOrder($marketplaceOrderId);

    public function persistOrder($order);

    public function confirmPayment($marketplaceOrderId, $orderId);

    public function cancelOrder($marketplaceOrderId, $orderId);

}