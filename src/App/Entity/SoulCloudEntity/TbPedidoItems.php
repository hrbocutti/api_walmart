<?php
namespace App\Entity\SoulCloudEntity;
use Illuminate\Database\Eloquent\Model as DBModel;

class TbPedidoItems extends DBModel
{
    protected $guarded = array('id');
    protected $table = "tb_pedido_items";
    public $timestamps = false;
}