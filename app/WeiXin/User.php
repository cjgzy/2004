<?php

namespace App\WeiXin;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table="user";	
    protected $primaryKey="m_id";
    public $timestamps=false;
    protected $guarded=[];
}
