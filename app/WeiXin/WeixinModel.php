<?php

namespace App\WeiXin;

use Illuminate\Database\Eloquent\Model;

class WeixinModel extends Model
{
    protected $table="users1";	
    protected $primaryKey="id";
    public $timestamps=false;
    protected $guarded=[];
}
