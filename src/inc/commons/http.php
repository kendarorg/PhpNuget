<?php

require_once(dirname(__FILE__)."/../gerfen/class.stream.php");
require_once(dirname(__FILE__)."/../compatibility.php");

class HttpUtils
{
	public static function HttpGet($url){
		return file_get_contents($url);
	}
	
	public static function HttpPost($url,$data,$contentType){
		// use key 'http' even if you send the request to https://...
		$options = array(
			'http' => array(
				'header'  => "Content-type: ".$contentType."\r\n",
				'method'  => 'POST',
				'content' => $data,
			),
		);
		$context  = stream_context_create($options);
		return file_get_contents($url, false, $context);
	}
	public static function ApiError($code, $message) {
		header('Status: ' . $code . ' ' . $message);
		http_response_code($code);
		header('Content-Type: text/plain');
		echo htmlspecialchars($message);
		die();
	}
	
	public static function WriteFile($path, $mime = "text/plain") {
		header('Content-Type: '.$mime);
		readfile($path);
		die();
	}
	
	public static function WriteData($data, $mime = "text/plain") {
		header('Content-Type: '.$mime);
		echo $data;
		die();
	}
	
	public static function RawRequest($onlyFiles = true)
	{
		$data = array();

		new stream($data);
		//$_PUT = $data['post'];
		
		return $data['file'];
	}
}

?>