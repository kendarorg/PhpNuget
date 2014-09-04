<?php
class Utils
{
	public static function NewGuid(){
		if (function_exists('com_create_guid')){
			return com_create_guid();
		}else{
			mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			$uuid = chr(123)// "{"
				.substr($charid, 0, 8).$hyphen
				.substr($charid, 8, 4).$hyphen
				.substr($charid,12, 4).$hyphen
				.substr($charid,16, 4).$hyphen
				.substr($charid,20,12)
				.chr(125);// "}"
			return $uuid;
		}
	}
	
	public static function WriteTemporaryFile($data = null)
	{
		$name = tempnam(sys_get_temp_dir(), '');
		if($data !=null){
			file_put_contents($name,$data);
		}
		return $name;
	}
	
	public static function FormatToIso8601Date($time=false) {
		if(!$time) $time=time();
		return date("Y-m-d", $time) . 'T' . date("H:i:s", $time) .'.000000Z';
	}
	
	public static function ReplaceInFile($template,$hash,$destination = null)
	{
		$content = file_get_contents($template);
		
		foreach ($hash as $key => $value){
			$content = str_replace($key,$value,$content);
		}
		
		if($destination!=null){
			file_put_contents($destination,$content);
		}
		
		return $content;
	}
}

function is_assoc_array($array) 
{
    if(!is_array($array))return false;
    return (bool)count(array_filter(array_keys($array), 'is_string'));
}

// Where,What
function starts_with($haystack, $needle)
{
    return !strncmp($haystack, $needle, strlen($needle));
}

// What,Where
function contains($needle, $haystack)
{
	if($needle==null) return false;
	if($haystack==null) return false;
	if(strlen($needle)>strlen($haystack))return false;
    return strpos($haystack, $needle) !== false;
}

// Where,What
function ends_with($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}
?>