<<<<<<< HEAD
<?php 
  if (!function_exists('http_response_code')) {
        function http_response_code($code = NULL) {

            if ($code !== NULL) {

                switch ($code) {
                    case 100: $text = 'Continue'; break;
                    case 101: $text = 'Switching Protocols'; break;
                    case 200: $text = 'OK'; break;
                    case 201: $text = 'Created'; break;
                    case 202: $text = 'Accepted'; break;
                    case 203: $text = 'Non-Authoritative Information'; break;
                    case 204: $text = 'No Content'; break;
                    case 205: $text = 'Reset Content'; break;
                    case 206: $text = 'Partial Content'; break;
                    case 300: $text = 'Multiple Choices'; break;
                    case 301: $text = 'Moved Permanently'; break;
                    case 302: $text = 'Moved Temporarily'; break;
                    case 303: $text = 'See Other'; break;
                    case 304: $text = 'Not Modified'; break;
                    case 305: $text = 'Use Proxy'; break;
                    case 400: $text = 'Bad Request'; break;
                    case 401: $text = 'Unauthorized'; break;
                    case 402: $text = 'Payment Required'; break;
                    case 403: $text = 'Forbidden'; break;
                    case 404: $text = 'Not Found'; break;
                    case 405: $text = 'Method Not Allowed'; break;
                    case 406: $text = 'Not Acceptable'; break;
                    case 407: $text = 'Proxy Authentication Required'; break;
                    case 408: $text = 'Request Time-out'; break;
                    case 409: $text = 'Conflict'; break;
                    case 410: $text = 'Gone'; break;
                    case 411: $text = 'Length Required'; break;
                    case 412: $text = 'Precondition Failed'; break;
                    case 413: $text = 'Request Entity Too Large'; break;
                    case 414: $text = 'Request-URI Too Large'; break;
                    case 415: $text = 'Unsupported Media Type'; break;
                    case 500: $text = 'Internal Server Error'; break;
                    case 501: $text = 'Not Implemented'; break;
                    case 502: $text = 'Bad Gateway'; break;
                    case 503: $text = 'Service Unavailable'; break;
                    case 504: $text = 'Gateway Time-out'; break;
                    case 505: $text = 'HTTP Version not supported'; break;
                    default:
                        exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
                }

                $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

                header($protocol . ' ' . $code . ' ' . $text);

                $GLOBALS['http_response_code'] = $code;

            } else {

                $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);

            }

            return $code;

        }
    }


require_once(dirname(__FILE__)."/../../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/api_users.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/http.php");
require_once(__ROOT__."/inc/api_nuget.php");




function respondBatch($batch) {
	$boundary = 'batchresponse_' . str_replace('"', '', mt_rand()) . '===';
	$data = '';
	foreach ($batch as $request) {
		$data .= '--' . $boundary . "\r\n"	
			. "Content-Type: application/http\r\n"
			. "Content-Transfer-Encoding: binary\r\n\r\n"
			. $request . "\r\n";
	}
	$data .= '--' . $boundary . '--';
	
		
	header("Content-Type: multipart/mixed; boundary=".$boundary);
	header("DataServiceVersion: 1.0");
	header("X-Content-Type-Options: nosniff");
	return $data;
}
	
function _parseBatchRequest($batchResponse) {
	$responses = array();
	// figuring out boundary value
	$boundaryStart = strpos($_SERVER["CONTENT_TYPE"], 'boundary=') + 9;
	$boundary = trim(substr($_SERVER["CONTENT_TYPE"], $boundaryStart));
	
	$rawResponses = explode("--$boundary", $batchResponse);
	array_shift($rawResponses);
	array_pop($rawResponses);
	foreach ($rawResponses as $response) {
		$response = str_replace("\r", "", $response);
		$responseStart = strpos($response, "\n\n") ;
		$response = trim(substr($response, $responseStart));
		$responses[] = trim ( $response,  " \t\n\r\0\x0B-"  );
	}
	return $responses;
}

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function acceptedHeader($header) {
	return startsWith($header, 'Content-Type') ||
		   startsWith($header, 'X-Content-Type-Options') ||
		   startsWith($header, 'Cache-Control');
}

function get_contents($url) {
	$headers = apache_request_headers();
	
	$opts = array('http' =>
		array(
			'header'  => 'Authorization: ' . $_SERVER['HTTP_AUTHORIZATION']
		)
	);

	$context  = stream_context_create($opts);
	$content = file_get_contents($url, false, $context);
	$headers = $http_response_header;

	$header = "DataServiceVersion: 2.0;\n" . implode("\n", array_filter( $headers, "acceptedHeader"));
	return $header . "\n\n" . $content;
}


$content = @file_get_contents('php://input');

foreach (_parseBatchRequest($content) as $response) {
	$info = explode(" ", $response);
	/*
	$url = parse_url($info[1]);
	
	parse_str($url["query"], $_GET);
	
	$pathStart = strpos($url["path"], "v2/") + 3;
	$path = explode("/", substr($url["path"], $pathStart));
	
	$_GET["action"] = strtolower(trim( $path[0],  " \t\n\r\0\x0B-()\$"  ));		
	$count = end((array_values($path)));
	if ($count=='$count') {
		$_GET["count"] = true;
	}
	*/
	$out2 = get_contents($info[1]);
	$batch[] = $out2;
	
}
http_response_code(202);
echo respondBatch($batch);
=======
<?php

require_once(dirname(__FILE__)."/../../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/api_users.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/http.php");
require_once(__ROOT__."/inc/api_nuget.php");

$v2BatchDebug = false;

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
			if(indexOf($res->Action,"http")!=0){
				$res->Action = UrlUtils::CurrentUrl($res->Action);
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
		$boundary = trim($boundary,'\"');
		
		//$boundary = "--".$boundary;
		// split content by boundary and get rid of last -- element
		$a_blocks =  explode("--".$boundary,$input); // preg_split("/-+$boundary/", $input);
		//echo $boundary."  ";
		//var_dump($input);die();
		$result = array();
		
		foreach ($a_blocks as $block){
			if (empty($block)){
				
				continue;
			}
			
			$splitted = preg_split('/\R/',$block);
			
			
			$subBatch = $this->BuildSubBatch($splitted);
			
			if($subBatch->Action!=""){
				array_push($result,$subBatch);
			}
		}
		
		
		return $result;
	}
	
	public function Elaborate($requests){
		$randBound = randomNumber(strlen("ff6a932f-7ca9-4926-9ae0-0e12776eacbf"));
		$randBoundSub = randomNumber(strlen("ff6a932f-7ca9-4926-9ae0-0e12776eacbf"));
		$boundary = "batchresponse_".$randBound;
		
		http_response_code(202); //accepted
		
		
		
		$result = "";
		
		header("DataServiceVersion: 1.0;");
		header("Content-Type: multipart/mixed; boundary=".$boundary);
		header("Cache-Control: no-cache");
		//header("Content-Type: multipart/mixed");
		//header("X-Content-Type-Options: nosniff");
		//header("X-XSS-Protection: 1; mode=block");
		for($i=0;$i<sizeof($requests);$i++){
			$request = $requests[$i];
			$result .= "--".$boundary."\r\n";
			$result .= "Content-Type: application/http\r\n";
			$result .="Content-Transfer-Encoding: binary\r\n";
			
			$result.="\r\n";
			$result .= "HTTP/1.1 ".$request->ResultStatus." ";
			if($request->ResultStatus==200){
				$result.="OK\r\n";
			}else{
				$result.="KO\r\n";
			}
			$result .="Cache-Control: no-cache\r\n";
			$result .="DataServiceVersion: 2.0;\r\n";
			if(is_numeric($request->ResultData)){
				$result.="Content-Type: text/plain;charset=utf-8\r\n";
			}else{
				$result.="Content-Type: application/atom+xml;type=feed;charset=utf-8\r\n";
			}
			if($request->ContentId!=null){
				$result .= "Content-ID: ".$request->ContentId."\r\n";
			}
			$result.="Content-Length: ".strlen($request->ResultData)."\r\n";
			
			$result.="\r\n";
			$result.=$request->ResultData."\r\n";
		}
		$result .= "--".$boundary."--\r\n";
		
		//file_put_contents("batch.log","RESULT:\r\n".$result."\r\nENDOFRESULT", FILE_APPEND);
		header("Content-Length: ".strlen($result));
		

		echo $result;
		flush();
		
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
		
		/*var_dump($input);
		flush();
		die();*/
		

		// grab multipart boundary from content type header
		preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);

		// content type is probably regular form-encoded
		if (!count($matches)){
			return null;
		}

		$boundary = $matches[1];
		
		
		if($v2BatchDebug){
			file_put_contents("batch.log","REQUEST:".$input."\r\n", FILE_APPEND);
			file_put_contents("batch.log","\r\n", FILE_APPEND);
		}
		
		$parsed =  $this->ParseData($boundary,$input);
		
		$result = array();
		
		
		if($v2BatchDebug){
			file_put_contents("batch.log","PARSING:".sizeof($parsed)."\r\n", FILE_APPEND);
			file_put_contents("batch.log","\r\n", FILE_APPEND);
		}
		
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
			file_put_contents("batch.log","RESULT:".$response."\r\n", FILE_APPEND);
			file_put_contents("batch.log","\r\n", FILE_APPEND);
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
>>>>>>> kendarorg/master
