<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get("info",function(){
	phpinfo();
});
Route::get("test","TestController@test");
Route::get("test1","TestController@test1");
Route::post("indexs","TestController@index");
Route::get("access","TestController@access");
Route::get("admin","TestController@admin");
Route::get("code_token","TestController@code_token");



Route::get("text2","TestController@text2");
Route::post("text3","TestController@text3");
Route::post("text4",function(){
	$data=file_get_contents("php://input");
	$xml=simplexml_load_string($data);
	echo $xml->ToUserName;

});
Route::post("text5",function(){
	$post=json_decode();
	echo $post;
});