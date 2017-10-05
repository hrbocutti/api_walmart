<?php
namespace App\MVC\IModel;


interface ISoulCloudModel
{
    public function buscarPedido($orderNumber);

    public function buscarTodosPedidos();

    public function createPedido($order);

    public function createItems($id_pedido, $order);

    public function createCliente($id_pedido, $order);

    public function createAddress($id_pedido, $order, $type);

    public function createProcessamento($id_pedido);

    public function calcularTotalPedido($order);

    public function atualizaStatusProcessamento($id, $status);

}