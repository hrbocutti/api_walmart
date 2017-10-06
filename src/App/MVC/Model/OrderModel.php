<?php
namespace App\MVC\Model;

use App\Entity\SoulCloudEntity\TbPedidos;
use App\Entity\SoulCloudEntity\TbStatusProcessamento;
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

    public function confirmPayment($marketplaceOrderId, $body)
    {
        $db = new DbFactory();
        $db->fatoryConnection('localhost','poli_gerencia2','root','');

        $orderId = json_decode($body);

        $pedido = TbPedidos::where('id' , '=', $orderId->orderId)->first();

        if (!empty($pedido)){
            $pedido->status = 'Payment Confirmed';
            $pedido->save();
        }else{
            return null;
        }

        $statusProcessamento = TbStatusProcessamento::where('tb_pedido_id', '=', $orderId->orderId)->first();
        if(!empty($statusProcessamento)){
            if ($statusProcessamento->status != 'processar_magento'){
                $statusProcessamento->status = 'processar_magento';
                $statusProcessamento->save();
            }
        }else{
            return null;
        }

        return $orderId;

    }

    public function cancelOrder($marketplaceOrderId, $orderId)
    {
        // TODO: Implement cancelOrder() method.
    }

}