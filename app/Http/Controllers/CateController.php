<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Log;
class CateController extends Controller
{
    public function test(){
    	$data=file_get_contents("php://input");
    	Log::info("========",$data);
    	$pos=simplexml_load_string($data);
    }
}
