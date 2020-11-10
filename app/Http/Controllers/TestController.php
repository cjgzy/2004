<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use DB;
use Log;
use App\WeiXin\WeixinModel;
use GuzzleHttp\Client;
class TestController extends Controller
{
  public function index(){
    	$res=$this->text();
          //创建菜单
        $res1=$this->create_moun();
        echo $res1;
    } 
      //自动回复
    public function  text()
    {
        //接收数据
        $data = file_get_contents("php://input");
        Log::info("=====接收数据====" . $data);
        //转换成对象
        $postarray = simplexml_load_string($data);
        $access_token = $this->access();//获取token
        if($postarray->MsgType=="text"){
                if($postarray->Content=="天气"){
                    $Content = $this->getweather();
                    $this->info($postarray,$Content);
                }
            }
        $openid = $postarray->FromUserName;//获取发送方的 openid
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        // Log::info("123456",$url);
        $user = json_decode($this->http_get($url),true);
        $WexiinModel = new WeixinModel;
        $first = WeixinModel::where("openid",$user["openid"])->first();
        if ($postarray->MsgType=="event") {
            if ($postarray->EventKey=="V1001_TODAY_QQ") {
               $Content="请输入你想看的新闻";
               $this->info($postarray,$Content);
            }
        }
        if ($first) {
            $array = ["欢迎回来!!!!"];
            $Content = $array[array_rand($array,1)];
            $this->info($postarray,$Content);die;
            
        } else {

            if ($postarray->MsgType == "event") {
                if ($postarray->Event == "subscribe") {
                    $array = ["你好啊", "欢迎关注!!!"];
                    $Content = $array[array_rand($array, 1)];
                    $this->info($postarray, $Content);
                    //入库
                    $data = [
                        "openid" => $user["openid"],
                        "city" => $user["city"],
                        "sex" => $user["sex"],
                        "language" => $user["language"],
                        "province" => $user["province"],
                        "country" => $user["country"],
                        "subscribe_time" => $user["subscribe_time"],
                        "subscribe" => $user["subscribe"],
                        "subscribe_scene" => $user["subscribe_scene"],
                    ];
                    $WexiinModel->insert($data);
                }
            }
        }
    }
	public function info($postarray,$Content){
		$ToUserName=$postarray->FromUserName;
		$FromUserName=$postarray->ToUserName;
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
    public function xinwen($Content){
        $key="04f4d3a7b600d4507956005d77a1c62e";
        $top=$Content;
        $url="http://v.juhe.cn/toutiao/index?type=$top&key=$key";
        $xml=file_get_contents($url);
        Log::info("===================",$xml);
    }
    public function create_moun(){
    $access_token=$this->access();
    $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
    $menu='{
                 "button":[
                 {
                      "type":"click",
                      "name":"新闻",
                      "key":"V1001_TODAY_QQ"
                  },
                  {
                       "name":"giao",
                       "sub_button":[
                       {
                           "type":"view",
                           "name":"新闻",
                           "url":"http://www.baidu.com/"
                        },
                        {
                           "type":"click",
                           "name":"经典语录",
                           "key":"V1001_GOOD"
                        }]
                   }]
             }';
    $moun=$this->curl($url,$menu);
    return $moun;
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
			// $token=file_get_contents($url);	
		$Client=new Client();
		$response=$Client->request('GET',$url,['verify'=>false]);
		$json_str=$response->getBody();
		// dd($json_str);
		// dd($token);
		$token=json_decode($json_str,true);
		$token=$token['access_token'];
		// dd($token);
		Redis::setex("token",3600,$token);
		}
		return $token;
	}

     public function curl($url,$menu){
        //1.初始化
        $ch = curl_init();
        //2.设置
        curl_setopt($ch,CURLOPT_URL,$url);//设置提交地址
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);//设置返回值返回字符串
        curl_setopt($ch,CURLOPT_POST,1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS,$menu);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        //3.执行
        $output = curl_exec($ch);
        //关闭
        curl_close($ch);
        return $output;
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