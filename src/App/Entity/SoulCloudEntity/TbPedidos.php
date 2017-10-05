<?php
namespace App\Entity\SoulCloudEntity;
use Illuminate\Database\Eloquent\Model as DBModel;

class TbPedidos extends DBModel
{
    protected $guarded = array('id');
    protected $table = "tb_pedido";
    public $timestamps = false;
}