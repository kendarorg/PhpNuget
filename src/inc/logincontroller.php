<?php
require_once(dirname(__FILE__)."/../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/db_users.php");

$loginController = new LoginController();

class LoginController
{
	function _initialize()
	{
		session_start();
		$doLogin = UrlUtils::GetRequestParamOrDefault("DoLogin","xxx","all");
		if("false"==$doLogin) {
			session_unset();
			session_destroy(); 
			$this->RedirectIfNotLoggedIn();
			return;
		}
		
		if("true"!=$doLogin && array_key_exists ("UserId",$_SESSION)){
			$this->IsLoggedIn = true;
			$this->UserId = $_SESSION["UserId"];
			$this->Admin = $_SESSION["Admin"];
			$this->Packages = $_SESSION["Packages"];
		}else if("true"==$doLogin){
			$this->_login();	
			$location = UrlUtils::CurrentUrl(Settings::$SiteRoot);
			header("Location: ".$location);
			die();			
		}
	}
	
	function LoginController()
	{
		$this->_initialize();
	}
	
	function RedirectIfNotLoggedIn()
	{
		if($this->IsLoggedIn) return;
		$location = UrlUtils::CurrentUrl(Settings::$SiteRoot."?specialType=logon");
		header("Location: ".$location);
		die();
	}
	
	public $IsLoggedIn;
	public $UserId;
	public $Admin;
	public $Packages;

	function _login()
	{
		$doLogin = UrlUtils::GetRequestParamOrDefault("DoLogin","false","all");
		
		if("false"==$doLogin) {
			session_unset();
			session_destroy(); 
			return;
		}
		
		$uid = UrlUtils::GetRequestParam("UserId","post");
		$pwd = md5(UrlUtils::GetRequestParam("Password","post"));
		
		$udb = new UserDb();
		$user = null;

		$ar = $udb->GetAllRows();		
		
		foreach($ar as $row)
		{
			
			if($row->Enabled && strtolower($uid) == strtolower($row->Email) || $uid == $row->UserId){
				if($pwd==$row->Md5Password){
					$user = $row;
					break;
				}
			}
		}
		
		
		//echo "Loggedin ".$doLogin;
		if($user == null){
			session_unset();
			session_destroy(); 
			return;
		}
		$this->IsLoggedIn = true;
		$this->UserId = $row->UserId;
		$this->Admin = $row->Admin;
		$this->Packages = $row->Packages;
		$_SESSION["UserId"] = $this->UserId;
		$_SESSION["Admin"] = $this->Admin;
		$_SESSION["Packages"] = $this->Packages;
	}
}

?>