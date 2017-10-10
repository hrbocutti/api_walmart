<?php
namespace App\MVC\Controller;

use App\MVC\Model\SoulCloudModel;
use App\MVC\IController\ISoulCloudController;

class SoulCloudController implements ISoulCloudController
{

    public function createOrder($order)
    {
        $model = new SoulCloudModel();

        //valida Order Primeiro
        $model->validadeOrder($order);
        $id = $model->createPedido($order);
        if (!empty($id)){
            $model->createItems($id, $order);
            $model->createCliente($id, $order);
            $model->createAddress($id, $order,"AddressBilling");
            $model->createAddress($id, $order,"AddressShipping");
            $model->createProcessamento($id);
        }
        return $id;

    }

    public function updateStatusOrder($orderNumber, $status)
    {
    }
}