<?php
namespace App\MVC\Model;
use App\Entity\SoulCloudEntity\TbCliente;
use App\Entity\SoulCloudEntity\TbPedidoAddress;
use App\Entity\SoulCloudEntity\TbPedidoItems;
use App\Entity\SoulCloudEntity\TbPedidos;
use App\Entity\SoulCloudEntity\TbStatusProcessamento;
use App\MVC\IModel\ISoulCloudModel;

class SoulCloudModel implements ISoulCloudModel
{

    public function buscarPedido($orderNumber)
    {
        $pedido = TbPedidos::where("order_number", "=",$orderNumber)->first();
        return $pedido;
    }

    public function buscarTodosPedidos()
    {
        $pedido = TbPedidos::where('tb_marketplace_id', '=', '10')->first();
        return $pedido;
    }

    public function createPedido($order)
    {
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
            try {
                $items = TbPedidoItems::create(array(
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
            } catch (\Exception $e) {
                header("HTTP/1.1". $e->getCode() ." ".$e->getMessage());
                die();
            }
        }
    }

    public function createCliente($id_pedido, $order)
    {
        $pfOrPj = ($order->clientProfileData->corporateDocument != null)? "CNPJ":"CPF";
        $email = $order->clientProfileData->email;
        $numDocumento = ($order->clientProfileData->corporateDocument != null)? $order->clientProfileData->corporateDocument:$order->clientProfileData->document;

        if ($pfOrPj == "CPF"){
            $clienteName = $order->clientProfileData->firstName . " " . $order->clientProfileData->lastName;
            $phone = $order->clientProfileData->phone;
        }else{
            $clienteName = $order->clientProfileData->corporateName;
            $phone = $order->clientProfileData->corporatePhone;
        }

        try{
            $cliente = TbCliente::create(array(
                "nome" => utf8_decode($clienteName),
                "cpf" => $numDocumento,
                "email" => $email,
                "telefone" => $phone,
                "telefone_contato" => $phone,
                "tb_pedido_id" => $id_pedido));
        }catch(\Exception $e){
            header("HTTP/1.1". $e->getCode() ." ".$e->getMessage());
            die();
        }
    }

    public function createAddress($id_pedido, $order, $type)
    {
        $shippingInfo = $order->shippingData;
        $email = $order->clientProfileData->email;
        $pfOrPj = ($order->clientProfileData->corporateDocument != null)? "CNPJ":"CPF";

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
        $idMkt = 10;
        try{
            $processamento = TbStatusProcessamento::create(array(
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
        $dbSelect = new DbFactory();
        $dbSelect->fatoryConnection("45.56.103.184", "poli_gerencia2","house_loja", "nxauLMNUevdj7SZR");
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
}