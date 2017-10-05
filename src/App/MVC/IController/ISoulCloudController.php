<?php
namespace App\MVC\IController;


interface ISoulCloudController
{
    public function createOrder($order);

    public function updateStatusOrder($orderNumber, $status);
}