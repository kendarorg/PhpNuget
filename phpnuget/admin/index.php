<?php
session_start();
define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/settings.php'); 
require_once(__ROOT__.'/inc/usersdb.php'); 
require_once(__ROOT__.'/inc/virtualdirectory.php'); 
require_once(__ROOT__.'/inc/utils.php'); 
require_once(__ROOT__.'/inc/login.php'); 


if(!Login()){
?>
<html>
    <body>
        <form id='login' action='index.php' method='post' accept-charset='UTF-8'>
            <fieldset >
                <legend>Login</legend>
                <input type='hidden' name='submitted' id='submitted' value='1'/>
                 
                <label for='username' >UserName*:</label>
                <input type='text' name='username' id='username'  maxlength="50" />
                 
                <label for='password' >Password*:</label>
                <input type='password' name='password' id='password' maxlength="50" />
                 
                <input type='submit' name='Submit' value='Submit' />
             
            </fieldset>
        </form>
    </body>
</html>
<?php
}else {
?>
<html>
    <body>
        
         <a href="pkg_index.php">Packages manager</a><br>
         <a href="usr_index.php">Users manager</a>
    </body>
</html>
<?php
}
?>