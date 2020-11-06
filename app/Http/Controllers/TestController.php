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
    public function index(){
    	$res=$this->text();
    	if ($res) {
    		echo $_GET['echostr'];
    	}

    }
    public function text(){
    	$signature = $_GET["signature"];
	    $timestamp = $_GET["timestamp"];
	    $nonce = $_GET["nonce"];
		
	    $token = env("WX_TOKEN");
	    $tmpArr = array($token, $timestamp, $nonce);
	    sort($tmpArr, SORT_STRING);
	    $tmpStr = implode( $tmpArr );
	    $tmpStr = sha1( $tmpStr );
	    
	    if( $tmpStr == $signature ){
	        return true;
	    }else{
	        return false;
	    }
	}
}
