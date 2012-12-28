<?php
define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/settings.php'); 
require_once(__ROOT__.'/inc/usersdb.php'); 
require_once(__ROOT__.'/inc/virtualdirectory.php'); 
require_once(__ROOT__.'/inc/utils.php'); 

function Login()
{
    if(IsLoggedIn()) return true;
    if(empty($_POST['username']))
    {
       // $this->HandleError("UserName is empty!");
        return false;
    }
     
    if(empty($_POST['password']))
    {
        //$this->HandleError("Password is empty!");
        return false;
    }
     
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
     
    $nugetReader = new UserDb();
    $allEntities = $nugetReader->GetAllRows();

    $userFounded = false;
    $isadmin = false;
    for($i=0;$i<sizeof($allEntities);$i++){
        $entity = $allEntities[$i];
        if((strtolower($entity->UserId)==strtolower($username)) && strtolower($entity->Md5Password)== strtolower(md5($password))){
            $isadmin = $entity->Admin=="true"? true:false;
            $userFounded = true;
            break;
        }
    }
    if(!$userFounded){
        $_SESSION[__USERNAMEVAR__]=null;
        $_SESSION[__ISADMINVAR__]=null;
        session_destroy();
        return false;
    }
    
     
    $_SESSION[__USERNAMEVAR__] = $username;
    $_SESSION[__ISADMINVAR__] = $isadmin;
    //session_write_close();
    return true;
}

function IsLoggedIn(){return $_SESSION[__USERNAMEVAR__]!=null && $_SESSION[__USERNAMEVAR__]!='';}
function IsAdmin(){return $_SESSION[__ISADMINVAR__]!=null && $_SESSION[__ISADMINVAR__]==true;}
function UserName(){return $_SESSION[__USERNAMEVAR__];}

function ManageLogin($isAdmin=false){
    session_start();
//    print_r($_SESSION);die();
     $canLogin = IsLoggedin();
     if($isAdmin==true && !IsAdmin()){
        $canLogin = false;
     }
     if(!$canLogin){
        ShowErrorLogin();
    }
}

function ShowErrorLogin()
{
        ?>
<html>
    <body>
        Not logged in!!!
    </body>
</html>        
        <?php
        die();   
}
?>