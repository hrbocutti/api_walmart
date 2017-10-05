<?php
namespace App\MVC\IModel;


interface IOrderModel
{
    public function findOrder($marketplaceOrderId);

    public function persistOrder($order);

}