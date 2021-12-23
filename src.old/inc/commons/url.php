<?php

		
class UrlUtils
{
	public static $_data;
	public static $_method;
	public static $_requestData;
	public static $_mainUrl;
	public static $_query;
	public static $_fake;
	
	public static function StaticInitialize(){
		UrlUtils::$_method = null;
		UrlUtils::$_requestData = null;
		UrlUtils::$_mainUrl = null;
		UrlUtils::$_query = null;
		UrlUtils::$_fake = false;
		
		if (!empty($_GET["_pq"])) {
			$query = urldecode($_GET["_pq"]);
			$gets = explode(",", $query);
			foreach ($gets as $get) {
				$item = explode("=", $get);
				$_GET[$item[0]] = trim($item[1]," '");
			}
			unset($_GET["_pq"]);
		}
	}
	
	public static function IsFake()
	{
		return UrlUtils::$_fake;
	}
	
	public static function ForceResponse($method,$url,$requestData){
		UrlUtils::$_method = strtolower($method);
		UrlUtils::$_requestData = json_encode($requestData);
		UrlUtils::$_mainUrl = UrlUtils::CurrentUrl(Settings::$SiteRoot);
		UrlUtils::$_query=array();
		$queryIndex = indexOf($url,"?");
		if($queryIndex>0){
			$query = substr($url,$queryIndex+1);
			UrlUtils::$_query = parse_str($query);
		}
		UrlUtils::$_fake = true;
	}
	
	public static function FileGetContents(){
		if(UrlUtils::$_requestData!=null) return UrlUtils::$_requestData;
		return file_get_contents("php://input");
	}
		
	public static function CurrentUrl($requestUri = "") {
        $pageURL = 'http';
        if ((array_key_exists("HTTPS",$_SERVER) && $_SERVER["HTTPS"] == "on") || 
			(array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER) && $_SERVER["HTTP_X_FORWARDED_PROTO"] = "https")) {
			$pageURL .= "s";
		}
		
        $pageURL .= "://";
		if($requestUri==""){
			$requestUri = $_SERVER["REQUEST_URI"];
			if(UrlUtils::$_mainUrl!=null){
				$requestUri = UrlUtils::$_mainUrl;
			}
		}
		$requestUri = trim($requestUri,"\\/");
        if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"]."/".$requestUri;
        } else {
			$pageURL .= $_SERVER["SERVER_NAME"]."/".$requestUri;
        }
        return $pageURL;
    }
	
	public static function GetUrlDirectory() {
		$uri = trim($_SERVER["REQUEST_URI"]);
		if(UrlUtils::$_mainUrl!=null){
			$uri = trim(UrlUtils::$_mainUrl);
		}
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
		if(UrlUtils::$_method!=null) return UrlUtils::$_method;
		return strtolower($_SERVER['REQUEST_METHOD']);
	}
	
	public static function InitializeJsonInput()
	{
		if(UrlUtils::$_data!=null) return;
		if(UrlUtils::RequestMethod()=="post" || UrlUtils::RequestMethod()=="put"){	
			$postdata = UrlUtils::FileGetContents();
			UrlUtils::$_data = json_decode($postdata, true);
		}
	}
	
	
	public static function ExistRequestParam($key,$verb = "all")
	{
		$test = "51f5efa7-d656-4a96-aa5a-c554e30ab10a###";
		return !(UrlUtils::GetRequestParamOrDefault($key,$test,$verb)===$test);
	}
	
	public static function GetRequestParam($key,$verb = "all")
	{
		return UrlUtils::GetRequestParamOrDefault($key,null,$verb);
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
		$q = array();
		if(UrlUtils::$_query!=null){
			$q = array_change_key_case(UrlUtils::$_query, CASE_LOWER);
		}
		$verb = strtolower($verb);
		$key = strtolower($key);
		
		if($verb=="all" || $verb=="get"){
			if(array_key_exists($key,$q)){
				return $q[$key];
			}
			$g = array_change_key_case($_GET, CASE_LOWER);
			if(array_key_exists($key,$g)){
				return $g[$key];
			}
		}
		if($verb=="all" || $verb=="post"){
			if(array_key_exists($key,$q)){
				return $q[$key];
			}
			$p = array_change_key_case($_POST, CASE_LOWER);
			if(array_key_exists($key,$p)){
				return $p[$key];
			}
		}
		if($verb=="all" || $verb=="put" || $verb=="post"){
			if(is_array(UrlUtils::$_data)){
				$d = array_change_key_case(UrlUtils::$_data, CASE_LOWER);
				if(array_key_exists($key,$d)){
					return $d[$key];
				}
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
UrlUtils::StaticInitialize();
?>