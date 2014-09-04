<?php
//require_once(__DIR__."/../root.php");
//require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/http.php");

class Result
{
	public $Message = "";
	public $Success = true;
	public $HttpErrorCode = 200;
	public $ApplicationErrorCode = 0;
	public $Data = null;
	public $CountAll = null;
}

class Pagination
{
	public $Skip = 0;
	public $Top = 10;

}

class ApiBase
{
	public $Data = array();
	public static function ReturnError($message,$httpErrorCode = 500,$applicationErrorCode=0)
	{
		$res = new Result();
		$res->Message = $message;
		$res->HttpErrorCode = $httpErrorCode;
		$res->ApplicationErrorCode = $applicationErrorCode;
		$res->Success = false;
		$json = json_encode($res);
		header('Content-Type: application/javascript');
		echo $json;
		die();
	}
	
	public static function ReturnErrorData($data,$prefix="",$postfix="",$count=0,$message,$httpErrorCode = 500,$applicationErrorCode=0)
	{
		$res = new Result();
		$res->Message = $message;
		$res->HttpErrorCode = $httpErrorCode;
		$res->ApplicationErrorCode = $applicationErrorCode;
		$res->Data = $data;
		$res->CountAll = $count;
		$res->Success = false;
		$json = $prefix.json_encode($res).$postfix;
		header('Content-Type: application/javascript');
		echo $json;
		die();
	}
	
	public static function ReturnSuccess($data,$prefix="",$postfix="",$count=0)
	{
		$res = new Result();
		$res->Success = true;
		$res->Data = $data;
		$res->CountAll = $count;
		$json = $prefix.json_encode($res).$postfix;
		header('Content-Type: application/javascript');
		echo $json;
		die();
	}
	
	public function Execute($method = null)
	{	
		if($method==null){
			$method = UrlUtils::RequestMethod();
			if(UrlUtils::ExistRequestParam("method")){
				$method = strtolower(UrlUtils::GetRequestParam("method"));
			}
		}
		
		$availableMethods = get_class_methods(get_class($this));
		$function = "do".$method;
		
		try{
			if(in_array($function,$availableMethods)){
				$this->$function();
			}else{
				ApiBase::ReturnError("Invalid method",405);
			}
		}catch(Exception $ex){
			ApiBase::ReturnError($ex->getMessage(),500);
		}
	}
	
	protected function _getPagination($top=10,$verbs = "all")
	{
		$pg = new Pagination();
		$pg->Skip = UrlUtils::GetRequestParamOrDefault("skip",0,$verbs);
		$pg->Top = UrlUtils::GetRequestParamOrDefault("top",$top,$verbs);
		return $pg;
	}
}

?>