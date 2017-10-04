<?php
/**
 * Created by PhpStorm.
 * User: Dev01
 * Date: 04/08/2017
 * Time: 15:05
 */

namespace App\Entity\SoulCloudEntity;
use Illuminate\Database\Eloquent\Model as DBModel;

class TbCliente extends DBModel
{
    protected $guarded = array('id');
    protected $table = "tb_cliente";
    public $timestamps = false;
}