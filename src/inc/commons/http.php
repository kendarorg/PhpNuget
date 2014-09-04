<?php
class HttpUtils
{
	public static function ApiError($code, $message) {
		header('Status: ' . $code . ' ' . $message);
		header('Content-Type: text/plain');
		echo htmlspecialchars($message);
		die();
	}
	
	public static function WriteFile($path, $mime = "text/plain") {
		header('Content-Type: '.$mime);
		require_once($path);
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

		// grab multipart boundary from content type header
		preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);

		// content type is probably regular form-encoded
		if (!count($matches)){
			// we expect regular puts to containt a query string containing data
			parse_str(urldecode($input), $a_data);
			return $a_data;
		}

		$boundary = $matches[1];

		// split content by boundary and get rid of last -- element
		$a_blocks = preg_split("/-+$boundary/", $input);
		array_pop($a_blocks);

		// loop data blocks
		foreach ($a_blocks as $id => $block){
			if (empty($block))
				continue;

			// you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char

			// parse uploaded files
			if (strpos($block, 'application/octet-stream') !== FALSE){
				// match "name", then everything after "stream" (optional) except for prepending newlines
				preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
				$a_data['files'][$matches[1]] = array();
				$a_data['files'][$matches[1]]["tmp_name"]=Utils::WriteTemporaryFile($matches[2]);
				$a_data['files'][$matches[1]]["type"]="";
				$a_data['files'][$matches[1]]["size"]=filesize($a_data['files'][$matches[1]]["tmp_name"]);
				$a_data['files'][$matches[1]]["error"]=0;
				$a_data['files'][$matches[1]]["name"]="name";
			}else{
				// parse all other fields
				// match "name" and optional value in between newline sequences
				preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
				$a_data[$matches[1]] = $matches[2];
			}
		}
		var_dump($a_data);
		if($onlyFiles) {
			return $a_data["files"];
		}
		return $a_data;
	}
}