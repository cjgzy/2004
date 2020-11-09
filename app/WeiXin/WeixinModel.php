<?php

namespace App\WeiXin;

use Illuminate\Database\Eloquent\Model;

class WeixinModel extends Model
{
    protected $table="user";
    protected $primaryKey="id";
    public $timestamps=false;
}
