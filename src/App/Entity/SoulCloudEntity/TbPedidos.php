<?php
namespace App\Entity\SoulCloudEntity;
use Illuminate\Database\Eloquent\Model as DBModel;

/**
 * Created by PhpStorm.
 * User: Dev01
 * Date: 04/08/2017
 * Time: 09:55
 */
class TbPedidos extends DBModel
{
    protected $guarded = array('id');
    protected $table = "tb_pedido";
    public $timestamps = false;
}