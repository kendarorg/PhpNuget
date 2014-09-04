<?php

class UrlUtils
{
	public static $_data;
	
	public static function CurrentUrl($requestUri = "") {
        $pageURL = 'http';
        if (array_key_exists("HTTPS",$_SERVER) && $_SERVER["HTTPS"] == "on") {
			$pageURL .= "s";
		}
		
        $pageURL .= "://";
		if($requestUri==""){
			$requestUri = $_SERVER["REQUEST_URI"];
		}
		$requestUri = trim($requestUri,"/");
        if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"]."/".$requestUri;
        } else {
			$pageURL .= $_SERVER["SERVER_NAME"]."/".$requestUri;
        }
        return $pageURL;
    }
	
	public static function GetUrlDirectory() {
		$uri = trim($_SERVER["REQUEST_URI"]);
		$pos = strpos($uri, ".");
		if($pos===false){
			return $uri;
		}else{
			$lastSlash = strrpos($uri,"/");
			if($lastSlash===false){
				return $uri;
			}
			return trim(substr($uri,0,$lastSlash),"/");
		}
    }
	
	public static function Combine($root,$path)
	{
		$root = rtrim($root,"\\/");
		$path = ltrim($path,"\\/");
		if(strlen($root)==0) return $path;
		if(strlen($path)==0) return $root;
		return $root."/".$path;
	}
	
	public static function RequestMethod()
	{
		return strtolower($_SERVER['REQUEST_METHOD']);
	}
	
	public static function InitializeJsonInput()
	{
		if(UrlUtils::$_data!=null) return;
		if(UrlUtils::RequestMethod()=="post" || UrlUtils::RequestMethod()=="put"){	
			$postdata = file_get_contents("php://input");
			UrlUtils::$_data = json_decode($postdata, true);
		}
	}
	
	
	public static function ExistRequestParam($key,$verb = "all")
	{
		$verb = strtolower($verb);
		if($verb=="all" || $verb=="get"){
			if(array_key_exists($key,$_GET)){
				return true;
			}
		}
		if($verb=="all" || $verb=="post"){
			if(array_key_exists($key,$_POST)){
				return true;
			}
		}
		if($verb=="all" || $verb=="put" || $verb=="post"){
			if(is_array(UrlUtils::$_data) && array_key_exists($key,UrlUtils::$_data)){
				return true;
			}
		}
		return false;
	}
	
	public static function GetRequestParam($key,$verb = "all")
	{
		$verb = strtolower($verb);
		if($verb=="all" || $verb=="get"){
			if(array_key_exists($key,$_GET)){
				return $_GET[$key];
			}
		}
		if($verb=="all" || $verb=="post"){
			if(array_key_exists($key,$_POST)){
				return $_POST[$key];
			}
		}
		if($verb=="all" || $verb=="put" || $verb=="post"){
			if(is_array(UrlUtils::$_data) && array_key_exists($key,UrlUtils::$_data)){
				return UrlUtils::$_data[$key];
			}
		}
		return null;
	}
	
	public static function GetBooleanRequestParam($key,$verb = "all")
	{
		$var = UrlUtils::GetRequestParam($key,$verb);
		if(strtolower($var) =="true" || strtolower($var) =="yes"){
			return "true";
		}
		return "false";
	}
	
	public static function GetRequestParamOrDefault($key,$default,$verb = "all")
	{
		$verb = strtolower($verb);
		if($verb=="all" || $verb=="get"){
			if(array_key_exists($key,$_GET)){
				return $_GET[$key];
			}
		}
		if($verb=="all" || $verb=="post"){
			if(array_key_exists($key,$_POST)){
				return $_POST[$key];
			}
		}
		if($verb=="all" || $verb=="put" || $verb=="post"){
			if(is_array(UrlUtils::$_data) && array_key_exists($key,UrlUtils::$_data)){
				return UrlUtils::$_data[$key];
			}
		}
		return $default;
	}
	
	public static function SafeBase64encode($string) 
	{ 
		$data = base64_encode($string); 
		$data = str_replace(array('+','/','='),array('-','_',''),$data); 
		return $data; 
	}
}
?>