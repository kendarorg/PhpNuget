<?php
define('__ROOT__',dirname( dirname(__FILE__)));

class UserEntity
{
    var $Admin="false";
    var $UserId;
    var $Name;
    var $Company;
    var $Md5Password;
    var $Packages;
    var $Enabled;
    var $Email;
    var $Token;
}
?>