<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use DB;
use Log;
use WeiXin\WeixinModel;
class TestController extends Controller
{
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
	    $tmpStr = implode($tmpArr);
	    $tmpStr = sha1($tmpStr);
	    
	    if( $tmpStr == $signature ){

	    	$xml_str=file_get_contents("php://input");
	    	log::info($xml_str);
	    	//将json转换成数组
	    	$pos=simplexml_load_string($xml_str);
	    	 if($pos->MsgType=="event"){
            if($pos->Event=="subscribe"){
                $Content = "谢谢关注！！！";
                $this->info($pos,$Content);
                $openid = $pos->FromUserName;//获取发送方的 openid
                $access_token = $this->access();//获取token
                $url ="https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
             	dd($url);
                $user = json_decode($this->http_get($url),true);
                if (isset($user["errcode"])) {
                    Log::info("====获取用户信息失败s=====".$user["errcode"]);
                    $this->writeLog("获取用户信息失败s");
                }else{
                    $WexiinModel = new WeixinModel;
                    $first = WeixinModel::where("openid",$user["openid"])->first();
                    if($first){
                        WeixinModel::where("openid",$user['openid'])->update(['subscribe'=>1]);
                    }else{
                        $data =[
                            "openid"=>$user["openid"],
                            "city"=>$user["city"],
                            "sex"=>$user["sex"],
                            "language"=>$user["language"],
                            "province"=>$user["province"],
                            "country"=>$user["country"],
                            "subscribe_time"=>$user["subscribe_time"],
                            "subscribe"=>$user["subscribe"],
                            "subscribe_scene"=>$user["subscribe_scene"],
                        ];
                        $WexiinModel->insert($data);
                    }
                }
            }
        }
          }
          if($pos->MsgType=="text"){
            if($pos->Content=="天气"){
                $Content = $this->getweather();
                $this->info($pos,$Content);
        }
	    }
	    
	    
	}

	public function info($pos,$Content){
		$ToUserName=$pos->FromUserName;
		$FromUserName=$pos->ToUserName;
		$CreateTime=time();
		$MsgType="text";
		   	$xml="<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%s</CreateTime>
  <MsgType><![CDATA[%s]]></MsgType>
  <Content><![CDATA[%s]]></Content>
</xml>";
	$info=sprintf($xml,$ToUserName,$FromUserName,$CreateTime,$MsgType,$Content);
	Log::info($info);
	echo $info;

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
		Redis::setex("token",3600,$token);
		}
		dd($token);
	}
	    //回复天气模板
    public function getweather(){
        $url = "http://api.k780.com:88/?app=weather.future&weaid=beijing&&appkey=10003&sign=b59bc3ef6191eb9f747dd4e83c99f2a4&format=json";
        $weather = file_get_contents($url);
        $weather = json_decode($weather,true);
        //dd($weather);
        // $aa = $weather["result"];
        // dd($aa);
        if($weather["success"]){
            $content = "";
            foreach($weather["result"] as $v){
                $content .= "地区:".$v['citynm']."日期:".$v['days']."温度:".$v['temperature']."风速:".$v['winp']."天气:".$v['weather'];
            }
        }
        return $content;
        Log::info("============".$weather);

    }
    public function http_get($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);//向那个url地址上面发送
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);//设置发送http请求时需不需要证书
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置发送成功后要不要输出1 不输出，0输出
        $output = curl_exec($ch);//执行
        curl_close($ch);    //关闭
        return $output;
 }

}