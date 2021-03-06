<?php
namespace App\MVC\Model;

use App\Entity\SoulCloudEntity\TbPedidoItems;
use App\Entity\SoulCloudEntity\TbPedidos;
use App\Entity\SoulCloudEntity\TbStatusProcessamento;
use App\Entity\TbPostingdaysNs;
use App\Factory\DbFactory;
use App\MVC\IModel\IOrderModel;
use App\MVC\Controller\SoulCloudController;
use App\Utilities\SendEmail;


class OrderModel implements IOrderModel
{
    protected $host, $database, $user, $password;

    function __construct()
    {
       $this->host = "45.56.103.184";
       $this->database = "poli_gerencia2";
       $this->user = "house_loja";
       $this->password = "nxauLMNUevdj7SZR";
    }

    public function findOrder($marketplaceOrderId)
    {
        $db = new DbFactory();
        $db->factoryConnection($this->host, $this->database, $this->user, $this->password);

        $pedido = TbPedidos::where('order_number', '=', $marketplaceOrderId)->first();
        $id = array("orderId" => (String) $pedido["id"]);
        return (empty($pedido))? null:$id;
    }

    public function persistOrder($order)
    {
        $soulCloud = new SoulCloudController();
        $id = $soulCloud->createOrder($order);
        return array("orderId" => (string) $id);
    }

    public function confirmPayment($marketplaceOrderId, $body)
    {
        $db = new DbFactory();
        $db->factoryConnection($this->host, $this->database, $this->user, $this->password);

        $orderId = json_decode($body);
        if(empty((array)$orderId)){
            header('HTTP 1/1', null, '400');
            echo json_encode(array("summary" => "Invalid body."));
            die();
        }

        $pedido = TbPedidos::where('id' , '=', $orderId->orderId)->first();

        if(empty($pedido)){
            header('HTTP 1/1', null, '400');
            echo json_encode(array("summary" => "Order not found."));
            die();
        }

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

    public function cancelOrder($marketplaceOrderId, $body)
    {
        $db = new DbFactory();
        $db->factoryConnection($this->host, $this->database, $this->user, $this->password);

        $orderId = json_decode($body);

        if (empty($orderId)){
            header('HTTP 1/1', null, '400');
            echo json_encode(array("summary" => "Cannot possible cancel order, the json are invalid."));
            die();
        }

        $pedido = TbPedidos::where('id' , '=', $orderId->orderId)->first();

        if (!empty($pedido)){
            $pedido->status = 'Canceled';
            $pedido->save();
        }else{
            return null;
        }

        $statusProcessamento = TbStatusProcessamento::where('tb_pedido_id', '=', $orderId->orderId)->first();
        if(!empty($statusProcessamento)){
            if ($statusProcessamento->status != 'CANCELADO'){
                $statusProcessamento->status = 'CANCELADO';
                $statusProcessamento->save();

                $this->extornoEstoque($body);
                $msg = "Pedido: ".$marketplaceOrderId." Cancelado";
                $mail = new SendEmail();
                $mail->send('webmaster@polihouse.com','atendimento@polihouse.com.br',null, 'Cancelamento de Pedidos Walmart', $msg);
            }
        }else{
            return null;
        }
        return $orderId;
    }

    public function extornoEstoque($body)
    {
        $db = new DbFactory();
        $db->factoryConnection($this->host, $this->database, $this->user, $this->password);
        $orderId = json_decode($body);
        $items = TbPedidoItems::where('tb_pedido_id', '=', $orderId->orderId)->get();
        foreach ($items as $item) {
           $this->executaExtorno($item->sku, $item->qty);
        }
    }

    public function executaExtorno($sku, $qty)
    {
        try{
            $db = new DbFactory();
            $db->factoryLocal();

            $item = TbPostingdaysNs::where('sku', '=', $sku)->first();
            $item->qty = $item->qty + $qty;
            $item->save();
        }catch(\Exception $e){
            echo $e->getMessage();
            header("HTTP 1/1", null, $e->getCode());
            die();
        }
        return $item->id;
    }
}