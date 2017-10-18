<?php
namespace App\MVC\Model;
use App\Entity\SoulCloudEntity\TbCliente;
use App\Entity\SoulCloudEntity\TbPedidoAddress;
use App\Entity\SoulCloudEntity\TbPedidoItems;
use App\Entity\SoulCloudEntity\TbPedidos;
use App\Entity\SoulCloudEntity\TbStatusProcessamento;
use App\Entity\TbPostingdaysNs;
use App\Factory\DbFactory;
use App\MVC\IModel\ISoulCloudModel;

class SoulCloudModel implements ISoulCloudModel
{
    protected $host, $database, $user, $password;

    function __construct()
    {
        $this->host = "localhost";
        $this->database = "poli_gerencia2";
        $this->user = "root";
        $this->password = "";
    }

    public function buscarPedido($orderNumber)
    {
        $db = new DbFactory();
        $db->factoryConnection($this->host, $this->database, $this->user, $this->password);
        $pedido = TbPedidos::where("order_number", "=",$orderNumber)->first();
        return $pedido;
    }

    public function buscarTodosPedidos()
    {
        $db = new DbFactory();
        $db->factoryConnection($this->host, $this->database, $this->user, $this->password);
        $pedido = TbPedidos::where('tb_marketplace_id', '=', '10')->first();
        return $pedido;
    }

    public function createPedido($order)
    {
        $db = new DbFactory();
        $db->factoryConnection($this->host, $this->database, $this->user, $this->password);

        $clienteNome = $order->clientProfileData->firstName . " " . $order->clientProfileData->lastName;
        $totalPedido = $this->calcularTotalPedido($order);
        $idMkt = 10;
        $id = null;
        try{
            $pedido = TbPedidos::create(
                array(
                    "order_id" => $order->marketplaceOrderId,
                    "order_number" => $order->marketplaceOrderId,
                    "increment_id" => null,
                    "cliente_nome" => utf8_decode($clienteNome),
                    "total_pedido" => $totalPedido,
                    "metodo_entrega" => "default",
                    "status" => 'waiting',
                    "data" => date('Y-m-d'),
                    "tb_marketplace_id" => $idMkt));
            $id = $pedido->id;
        }catch (\Exception $e){
            header("HTTP/1.1". $e->getCode() ." ".$e->getMessage());
            die();
        }

        if ($id == null ){
            header("HTTP/1.1 500 Não foi possivel obter o ID");
            die();
        }
        return $id;
    }

    public function createItems($id_pedido, $order)
    {
        $totalFrete = $this->calcularFrete($order->shippingData->logisticsInfo);
        foreach ($order->items as $orderedItem) {
            $db = new DbFactory();
            $db->factoryConnection($this->host, $this->database, $this->user, $this->password);
            try {
                TbPedidoItems::create(array(
                "shop_id" => $orderedItem->id,
                "name" => "No Named",
                "sku" => $orderedItem->id,
                "qty" => $orderedItem->quantity,
                "mktplace_sku" => $orderedItem->id,
                "item_price" => $orderedItem->price,
                "item_paid" => $orderedItem->price,
                "tax_amount" => 0,
                "tax_percent" => 0,
                "shipping_amount" => $totalFrete,
                "tb_pedido_id" => $id_pedido));
                $this->baixaEstoque($orderedItem->id, $orderedItem->quantity);
            } catch (\Exception $e) {
                echo $e;
                header("HTTP/1.1". $e->getCode() ." ".$e->getMessage());
                die();
            }
        }
    }

    public function createCliente($id_pedido, $order)
    {
        $db = new DbFactory();
        $db->factoryConnection($this->host, $this->database, $this->user, $this->password);
        $pfOrPj = (!empty($order->clientProfileData->corporateDocument))? "CNPJ":"CPF";
        $email = $order->clientProfileData->email;
        $numDocumento = (!empty($order->clientProfileData->corporateDocument))? $order->clientProfileData->corporateDocument:$order->clientProfileData->document;

        if ($pfOrPj == "CPF"){
            $clienteName = $order->clientProfileData->firstName . " " . $order->clientProfileData->lastName;
            $phone = $order->clientProfileData->phone;
        }else{
            $clienteName = $order->clientProfileData->corporateName;
            $phone = $order->clientProfileData->corporatePhone;
        }

        try{
            TbCliente::create(array(
            "nome" => utf8_decode($clienteName),
            "cpf" => $numDocumento,
            "email" => $email,
            "telefone" => $phone,
            "telefone_contato" => $phone,
            "tb_pedido_id" => $id_pedido));
        }catch(\Exception $e){
            echo $e->getMessage();
            //header("HTTP/1.1". $e->getCode() ." ".$e->getMessage());
            die();
        }
    }

    public function createAddress($id_pedido, $order, $type)
    {
        $db = new DbFactory();
        $db->factoryConnection($this->host, $this->database, $this->user, $this->password);
        $shippingInfo = $order->shippingData;
        $email = $order->clientProfileData->email;
        $pfOrPj = (!empty($order->clientProfileData->corporateDocument))? "CNPJ":"CPF";

        if ($pfOrPj == "CPF"){
            $phone = $order->clientProfileData->phone;
        }else{
            $phone = $order->clientProfileData->corporatePhone;
        }

        $clienteName = $order->shippingData->address->receiverName;

        $zipCode = str_replace("-", "", $shippingInfo->address->postalCode);
        try{
            $addressShipping = TbPedidoAddress::create(array(
                "nome" => utf8_decode($clienteName),
                "phone" => $phone,
                "address_1" => utf8_decode($shippingInfo->address->street ). "," .utf8_decode($shippingInfo->address->number),
                "address_2" => utf8_decode($shippingInfo->address->complement) . " / " . utf8_decode($shippingInfo->address->reference),
                "email" => $email,
                "city" => utf8_decode($shippingInfo->address->city),
                "region" => utf8_decode($shippingInfo->address->neighborhood),
                "postal_code" => $zipCode,
                "country" => "BR",
                "type_address" => $type,
                "tb_pedido_id" => $id_pedido));

        }catch(\Exception $e){
            header("HTTP/1.1". $e->getCode() ." ".$e->getMessage());
            die();
        }
    }

    public function createProcessamento($id_pedido)
    {
        $db = new DbFactory();
        $db->factoryConnection($this->host, $this->database, $this->user, $this->password);

        $idMkt = 10;
        try{
            TbStatusProcessamento::create(array(
            "status" => "waiting",
            "tb_pedido_id" => $id_pedido,
            "tb_marketplace_id" => $idMkt));
        }catch(\Exception $e){
            header("HTTP/1.1". $e->getCode() ." ".$e->getMessage());
            die();
        }
    }

    public function calcularTotalPedido($order)
    {
        $total = 0;
        $totalFrete = $this->calcularFrete($order->shippingData->logisticsInfo);
        foreach ($order->items as $orderedItem) {
            $total = $total + ($orderedItem->price * $orderedItem->quantity);
        }

        $total = $total + $totalFrete;

        if($total == 0){
            header("HTTP/1.1 500 Total do pedido não pode ser igual a 0");
            die();
        }

        return $total;
    }

    public function atualizaStatusProcessamento($id, $status)
    {
        $db = new DbFactory();
        $db->factoryConnection($this->host, $this->database, $this->user, $this->password);
        $statusCode = null; // para retornar sucesso
        try{
            $process = TbStatusProcessamento::where('tb_pedido_id', $id)->first();
            $process->status = $status;
            $process->save();
            $statusCode = 200;
        }catch(\Exception $e){
            header("HTTP/1.1". $e->getCode() ." ".$e->getMessage());
            die();
        }
        return $statusCode;
    }

    public function calcularFrete($items)
    {
        $total = 0;
        foreach ($items as $item) {

            if (!empty($item->deliveryWindow->price)){
                $total = $total + $item->deliveryWindow->price;
            }else{
                $total = $total + $item->price;
            }

        }
        return $total;
    }

    public function baixaEstoque($sku, $qty)
    {
        $dbSelect = new DbFactory();
        $dbSelect->factoryLocal();
        $item = TbPostingdaysNs::where('sku', '=', $sku)->first();
        $qtyAtual = $item->qty;
        $item->qty = $qtyAtual - $qty;
        $item->save();
    }

    public function validadeOrder($order)
    {
        if (empty($order)){
            header("HTTP/1.1 400", null, 400);
            echo json_encode(array("summary" => "Invalid body"));
            die();
        }

        $items = $order->items;
        foreach ($items as $item) {
            if ($item->quantity <= 0 ){
                header("HTTP/1.1 400", null, 400);
                echo json_encode(array("summary" => "Invalid Body, quantity cannot be less than 0 !"));
                die();
            }

            //validate if has stock
            $sku = $item->id;

            $db = new DbFactory();
            $db->factoryLocal();
            $product = TbPostingdaysNs::where('sku', '=', $sku)->first();

            if (empty($product)){
                header("HTTP/1.1 400", null, 400);
                echo json_encode(array("summary" => "Invalid body"));
                die();
            }

            if (gettype($item->quantity) != "integer"){
                header("HTTP/1.1 400", null, 400);
                echo json_encode(array("summary" => "Invalid body"));
                die();
            }

            if($product->qty <= 0 ){
                header("HTTP/1.1 400", null, 400);
                echo json_encode(array("summary" => "Quantity available is not the quantity requested !"));
                die();
            }

            if($item->quantity > $product->qty){
                header("HTTP/1.1 400", null, 400);
                echo json_encode(array("summary" => "Quantity available is not the quantity requested !"));
                die();
            }
        }
    }
}