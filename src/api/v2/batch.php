<?php

require_once(dirname(__FILE__)."/../../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/api_users.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/http.php");
require_once(__ROOT__."/inc/api_nuget.php");

$v2BatchDebug = true;

if($v2BatchDebug){
	file_put_contents("batch.log","==================================\r\n", FILE_APPEND);
	file_put_contents("batch.log","request: ".$_SERVER['REQUEST_URI']."\r\n", FILE_APPEND);
	if(sizeof($_POST)>0){
		file_put_contents("batch.log",var_export($_POST,true)."\r\n", FILE_APPEND);
	}
	if(sizeof($_GET)>0){
		file_put_contents("batch.log",var_export($_GET,true)."\r\n", FILE_APPEND);
	}
}

class Batcher
{
	public function BuildSubBatch($src){
		global $v2BatchDebug;
		$res = new SubBatch();
		$i =0;
		$res->Action = null;
		$res->ContentId = null;
		for(;$i<sizeof($src);$i++){
			$li = $src[$i];
			
			if(starts_with($li,"Content-ID")) {
				$res->ContentId = substr($li,strlen("Content-ID:")+1);
			}else if(starts_with($li,"POST")) {
				$res->Action = substr($li,strlen("POST")+1);
				$res->Method = "post";
			}else if(starts_with($li,"GET")) {
				$res->Action =substr($li,strlen("GET")+1);
				$res->Method = "get";
				break;
			}else if(starts_with($li,"PUT")) {
				$res->Action = substr($li,strlen("PUT")+1);
				$res->Method = "put";
			}else if(starts_with($li,"DELETE")) {
				$res->Action = substr($li,strlen("DELETE")+1);
				$res->Method = "delete";
			}else if(starts_with($li,"Content-Length")) {
				$res->ContentLength = substr($li,strlen("Content-Length:")+1);
				$i+=2;
				break;
			}
		}
		if($res->Action!=null){
			
			$http = indexOf($res->Action," HTTP");
			if($http>0){
				$res->Action = substr($res->Action,0,$http);
			}
		}
		//Add the space after the method
		$res->Data = "";
		
		while($i<sizeof($src) && $res->Method!="get"){
			if($res->Data!=""){
				$res->Data .= "\n".$src[$i];
			}else{
				$res->Data .= $src[$i];
			}
			$i++;
		}
		
		return $res;
	}
	
	public function ParseData($boundary,$input){
		global $v2BatchDebug;
		
		//$boundary = "--".$boundary;
		// split content by boundary and get rid of last -- element
		$a_blocks =  preg_split("/-+$boundary/", $input);
		
		
		
		$result = [];
				
		foreach ($a_blocks as $block){
			if (empty($block))
				continue;
			
			$splitted = preg_split('/\R/',$block);
			
			
			$subBatch = $this->BuildSubBatch($splitted);
			if($subBatch->Action!=""){
				array_push($result,$subBatch);
			}
		}
		
		
		return $result;
	}
	
	public function Elaborate($requests){
		$randBound = randomNumber(strlen("pK7JBAk73-E=_AA5eFwv4m2Q="));
		$boundary = "batch_".$randBound;
		header("Content-Type: multipart/mixed; boundary=\"".$boundary."\"");
		$result = "";
		for($i=0;$i<sizeof($requests);$i++){
			$request = $requests[$i];
			
			$result .= "--".$boundary."\r\n";
			$result .= "Content-Type: application/http\r\n";
			$result .="Content-Transfer-Encoding: binary\r\n";
			if($request->ContentId!=null){
				$result .= "Content-ID: <response-".substr($request->ContentId,1)."\r\n";
			}
			$result.="\r\n";
			$result .= "HTTP/1.1 ".$request->ResultStatus." ";
			if($request->ResultStatus==200){
				$result.="OK\r\n";
			}else{
				$result.="KO\r\n";
			}
			$result .="Cache-Control: no-cache\r\n";
			$result .="DataServiceVersion: 2.0;\r\n";
			$result.="Content-Type: application/atom+xml;charset=utf-8\r\n";
			//$result.="Content-Length: ".(strlen($request->ResultData))."\r\n";
			$result.="\r\n";
			$result.=$request->ResultData."\r\n";
		}
		$result .= "--".$boundary."--\r\n";
		//header("Content-Length: ".strlen($result));
		return $result;
	}
	
	public function RawRequest()
	{
		global $v2BatchDebug;
		
		if(!array_key_exists('CONTENT_TYPE',$_SERVER) && UrlUtils::RequestMethod()!="post"){
			
			HttpUtils::ApiError(405,"The HTTP verb used is not allowed.");
		}
		$a_data = array();
		// read incoming data
		$input = file_get_contents('php://input');
		

		// grab multipart boundary from content type header
		preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);

		// content type is probably regular form-encoded
		if (!count($matches)){
			return null;
		}

		$boundary = $matches[1];
		
		$parsed =  $this->ParseData($boundary,$input);
		
		$result = [];
		for($i=0;$i<sizeof($parsed);$i++){
			$item = $parsed[$i];
			
			$item->ResultStatus = 200;
			
			if($item->Method=="get"){
				$item->ResultData = HttpUtils::HttpGet($item->Action);
			}else if($item->Method=="get"){
				$item->ResultData = HttpUtils::HttpPost($item->Action,$item->Data,"application/atom+xml");
			}
			array_push($result,$item);
		}
		
		
		$response =  Batcher::Elaborate($result);
		
		if($v2BatchDebug){
			file_put_contents("batch.log","ACTION:".$response."\r\n", FILE_APPEND);
		}
	}
}


class SubBatch
{
	var $ContentId;
	var $ContentLength;
	var $Action;
	var $Method;
	var $Data;
	var $ResultData;
	var $ResultStatus;
}

if(true){
$b = new Batcher();
$b->RawRequest();
}else{

$boundary = "===============7330845974216740156==";
$filePath = dirname(__FILE__).DIRECTORY_SEPARATOR."sampleBatchSimple.txt";
//var_dump($filePath);

$input = file_get_contents($filePath);
$b = new Batcher();
$result = $b->ParseData($boundary,$input);

$result[0]->ResultData="{0}";
$result[0]->ResultStatus=200;
$result[1]->ResultData="{1}";
$result[1]->ResultStatus=500;

$elab = $b->Elaborate($result);
var_dump($elab);
}
?>
