<?php
require_once(dirname(__FILE__)."/../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/db_users.php");
require_once(__ROOT__."/inc/compatibility.php");

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
			$this->Email = $_SESSION["Email"];
		}else if("true"==$doLogin){
			$this->_login();
			$location = UrlUtils::CurrentUrl(Settings::$SiteRoot);
			header("Location: ".$location);
			die();			
		}
	}
	
	function __construct()
	{
		$this->_initialize();
	}
	
	function RedirectIfNotLoggedIn($errorCode = 0)
	{
		if($this->IsLoggedIn) return;
		$result = "";
		switch($errorCode){
			case -1:
				$result = base64_encode("Invalid credentials");//"User does not exist.");
				break;
			case -2:
				$result = base64_encode("Invalid credentials");//"Incorrect password.");
				break;
			case -3:
				$result = base64_encode("Invalid credentials");//"This user is currently disabled.");
				break;
		}
		$location = UrlUtils::CurrentUrl(Settings::$SiteRoot."?specialType=logon&result=$result");
		header("Location: ".$location);
		die();
	}
	
	function UnauthorizedIfNotLoggedIn()
	{
		if($this->IsLoggedIn) return;
		http_response_code(403);
		echo "Unauthorized";
		die();
	}
	
	public $IsLoggedIn;
	public $UserId;
	public $Admin;
	public $Packages;
	public $Email;

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

		$ar = $udb->Query("(UserId eq '".$uid."' or Email eq '".$uid."')");
		$errorCode = 0;
			
		if(sizeof($ar)==1){
			$user = $ar[0];
			if($user->Md5Password != $pwd) {
				$errorCode = -2;
			} else if($user->Enabled != true) {
				$errorCode = -3;
			}
		} else {
			$errorCode = -1;
		}
		
		
		//echo "Loggedin ".$doLogin;
		if($errorCode != 0){
			session_unset();
			session_destroy();
			$this->RedirectIfNotLoggedIn($errorCode);
			return;
		}
		$this->IsLoggedIn = true;
		$this->UserId = $user->UserId;
		$this->Admin = $user->Admin;
		$this->Packages = $user->Packages;
		$this->Email = $user->Email;
		$_SESSION["UserId"] = $this->UserId;
		$_SESSION["Admin"] = $this->Admin;
		$_SESSION["Packages"] = $this->Packages;
		$_SESSION["Email"] = $this->Email;
	}
}

?>