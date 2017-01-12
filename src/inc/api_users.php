<?php
require_once(dirname(__FILE__)."/../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/commons/smalltextdbapibase.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/db_users.php");

class UsersApi extends SmallTextDbApiBase
{
	protected function _verifyInsert($db,$item)
	{
		if(!array_key_exists ("UserId",$_SESSION)){
			throw new Exception("Not logged in!");
		}
		$isAdmin = $_SESSION["Admin"]=="true" || $_SESSION["Admin"]==true;
		if(!$isAdmin){
			throw new Exception("Not authorized!");
		}
		
		UrlUtils::InitializeJsonInput();
		$udb = new UserDb();
		
		$user = $udb->GetByUserId($item->UserId);
		if($user!=null){
			throw new Exception("UserId duplicated!");
		}
		
		$passwordConfirm = UrlUtils::GetRequestParam("PasswordConfirm");
		$password = UrlUtils::GetRequestParam("Password");
		if($password!=$passwordConfirm){
			throw new Exception("Passwords must match!");
		}
		$validator = trim($password);
		if(!preg_match(__PWDREGEX__,$validator)){
			throw new Exception("Passwords not valid! ".__PWDDESC__);
		}
		
		$item->Md5Password = md5($password);
		$item->Enabled = UrlUtils::GetRequestParam("Enabled");
		$item->Admin = UrlUtils::GetRequestParam("Admin");
	}
	
	protected function _verifyUpdate($db,$old,$new)
	{
		if(!array_key_exists ("UserId",$_SESSION)){
			throw new Exception("Not logged in!");
		}
		
		
		$userId = $_SESSION["UserId"];
		$isAdmin = $_SESSION["Admin"]=="true" || $_SESSION["Admin"]==true;
		
		if($userId!=$old->UserId && $isAdmin==false){
			throw new Exception("Action allowed only on 'self'");
		}
		if($old->UserId != $new->UserId){
			throw new Exception("User Id can't be changed!'");
		}
		
		$token = UrlUtils::GetRequestParam("NewToken");
		if($token=="NewToken"){
			foreach($db->GetAllColumns() as $row)
			{
				$new->$row =$old->$row;
				
			}
			$new->Token = Utils::NewGuid();
			return;
		}
	
		$newPasswordConfirm = UrlUtils::GetRequestParam("NewPasswordConfirm");
		$newPassword = UrlUtils::GetRequestParam("NewPassword");
		
		
		$password =md5(UrlUtils::GetRequestParam("Password"));
		
		if($password!=$old->Md5Password && $isAdmin==false){
			throw new Exception("Authentication failed");
		}
		
		if(strlen($newPassword)>0){
			if($newPassword!=$newPasswordConfirm){
				throw new Exception("Passwords must match!");
			}
			if(strlen($newPassword)<8){
				throw new Exception("Passwords must be at least 8 chars wide!");
			}
			$password = md5($newPassword);
		}
		
		if($isAdmin==false){
			$new->Admin = $old->Admin;
			$new->Enabled = $old->Enabled;
		}
		
		if($isAdmin){
			$new->Enabled = UrlUtils::GetRequestParam("Enabled");
			$new->Admin = UrlUtils::GetRequestParam("Admin");
			$new->Packages = UrlUtils::GetRequestParam("Packages");
			if($password!=$old->Md5Password){
				$new->Md5Password = $password;
			}else{
				$new->Md5Password = $old->Md5Password;
			}
		}else{
			$new->Admin = $old->Admin;
			$new->Enabled = $old->Enabled;
			$new->Packages = $old->Packages;
			$new->Md5Password = $password;
		}
		
	}
	
	protected function _buildKeysFromRequest($db)
	{
		$result = array();
		$result["UserId"]= UrlUtils::GetRequestParam("UserId");
		return $result;
	}
	
	protected function _openDb()
	{
		return new UserDb();
	}
	
	protected function _buildEntityFromRequest($db)
	{
		$userEntity = new UserEntity();
		$error = true;
		foreach($db->GetAllColumns() as $row)
		{
			if(UrlUtils::ExistRequestParam($row)){
				$userEntity->$row =UrlUtils::GetRequestParam($row);
			}
		}
		
		return $userEntity;
	}
	
	protected function _preExecute()
	{
		UrlUtils::InitializeJsonInput();
	}
	
	protected function _isMatch($keyArray,$item)
	{
		return $keyArray == $item->UserId;
	}
}
?>