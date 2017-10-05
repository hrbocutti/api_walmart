<?php
namespace App\MVC\Model;

use App\Entity\SoulCloudEntity\TbPedidos;
use App\Factory\DbFactory;
use App\MVC\IModel\IOrderModel;
use App\MVC\Controller\SoulCloudController;


class OrderModel implements IOrderModel
{
    public function findOrder($marketplaceOrderId)
    {
        $db = new DbFactory();
        $db->fatoryConnection('localhost','poli_gerencia2','root','');

        $pedido = TbPedidos::where('order_number', '=', $marketplaceOrderId)->first();
        $id = array("orderId" => (String) $pedido["id"]);
        return (empty($pedido))? null:$id;
    }

    public function persistOrder($order)
    {
        $soulCloud = new SoulCloudController();
        $id = $soulCloud->createOrder($order);
        return json_encode(array("orderId" =>$id));
    }
}