<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use DB;
class TestController extends Controller
{
    public function test(){
    	$key="11111";
    	Redis::set($key,time());
    	echo Redis::get($key);
    }
    public function test1(){
    	$res=DB::table("test")->limit(4)->get()->Toarray();
    	var_dump($res);
    }
}
