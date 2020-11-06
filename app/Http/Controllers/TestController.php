<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use DB;
use Log;
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

	    	$xml_str=file_get_contents("php://input");
	    	file_put_contents("access_weixin.log",$xml_str);

// 	    	$xml="<xml>
//   <ToUserName><![CDATA[toUser]]></ToUserName>
//   <FromUserName><![CDATA[fromUser]]></FromUserName>
//   <CreateTime>12345678</CreateTime>
//   <MsgType><![CDATA[text]]></MsgType>
//   <Content><![CDATA[你好]]></Content>
// </xml>";

	    }
	}
	public function admin(){
		$res=$this->access();
		dd($res);
	}
	public function access(){
		$token=Redis::get("token");
		if (!$token) {
		// $stream_opts = [
		//     "ssl" => [
		//         "verify_peer"=>false,
		//         "verify_peer_name"=>false,
		//     ]
		// ]; 
		$url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx0698605a1ca84bf6&secret=4f806f9e3a01e61d063e175aaa103ee4";
		// $token=file_get_contents($url,false,stream_context_create($stream_opts));
			$token=file_get_contents($url);
		// dd($token);
		$token=json_decode($token,true);
		$token=$token['access_token'];
		// dd($token);
		Redis::setex("token",time()*60*60*24,$token);
		}
		dd($token);
	}
}
