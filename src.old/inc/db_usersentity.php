<?php
require_once(dirname(__FILE__)."/../root.php");
require_once(__ROOT__."/settings.php");

class UserEntity
{
    var $Id;
    var $Admin = "false";
    var $UserId;
    var $Name;
    var $Company;
    var $Md5Password;
    var $Packages;
    var $Enabled;
    var $Email;
    var $GravatarUrl;
    var $Token;
}
?>