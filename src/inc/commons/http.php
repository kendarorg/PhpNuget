<?php
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
		$a_data = array();
		// read incoming data
		$input = file_get_contents('php://input');
		
		//file_put_contents("upload.log","==================================\r\n".$input, FILE_APPEND);

		// grab multipart boundary from content type header
		preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);

		// content type is probably regular form-encoded
		if (!count($matches)){
			// we expect regular puts to containt a query string containing data
			parse_str(urldecode($input), $a_data);
			return $a_data;
		}

		$boundary = str_replace("\"","",$matches[1]);
		
		// split content by boundary and get rid of last -- element
		$a_blocks = preg_split("/-+$boundary/", $input);
		array_pop($a_blocks);

		// loop data blocks
		foreach ($a_blocks as $id => $block){
			if (empty($block) || $block=="--")
				continue;

			// you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char
			// parse uploaded files
			if (strpos($block, 'application/octet-stream') !== FALSE){
				// match "name", then everything after "stream" (optional) except for prepending newlines
				//preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
				preg_match("/((Content-Disposition[^\n\r]+[\n|\r]+)(Content-Type[^\n\r]+[\n|\r]+)|(Content-Type[^\n\r]+[\n|\r]+)(Content-Disposition[^\n\r]+[\n|\r]+))([^\n\r].*)?$/s", $block, $matches);

				$realData = $matches[6];
				
				$tmpFileName = "package";
				$a_data['files'][$tmpFileName] = array();
				$a_data['files'][$tmpFileName]["tmp_name"]=Utils::WriteTemporaryFile($realData);
				$a_data['files'][$tmpFileName]["type"]="";
				$a_data['files'][$tmpFileName]["size"]=filesize($a_data['files'][$tmpFileName]["tmp_name"]);
				$a_data['files'][$tmpFileName]["error"]=0;
				$a_data['files'][$tmpFileName]["name"]="name";
			}else{
				// parse all other fields
				// match "name" and optional value in between newline sequences
				preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
				if(sizeof($matches)>=2){
					$a_data[$matches[1]] = $matches[2];
				}
			}
		}
		
		if($onlyFiles) {
			return $a_data["files"];
		}
		return $a_data;
	}
}
