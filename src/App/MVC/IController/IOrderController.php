<?php
namespace App\MVC\IController;


interface IOrderController
{

    public function create($order);

    public function confirm($marketplaceOrderId);

    public function cancel($marketplaceOrderId);

}