<?php

class GlobalErrors{

    private $log;
    public function __construct()
    {
        $this->log  = LogManager::getLogger("GlobalErrors");
    }

    var $globalErrors =[];

    function getGlobalErrors(){
        return $this->globalErrors;
    }

    function addGlobalError(...$error){

        try{
            global $currentOperation;
            foreach ($error as &$e){
                if ($e instanceof Throwable) {
                    $this->log->error("Error on operation $currentOperation",$e);
                }else{
                    $this->log->error("Error on operation $currentOperation %s",$e);
                }
            }
        }catch (\Exception $e){

        }
        $admin = false;
        if(isset($_SESSION['user_role'])){
            $admin = $_SESSION['user_role'] === 'SuperAdmin' || $_SESSION['user_role'] === 'Admin';
        }
        for($i=0;$i<count($error);$i++) {
            $err = $error[$i];
            if($err instanceof Throwable) {
                if (!$admin) continue;
                $error[$i] = $err->getMessage();
            }
            if(str_starts_with(strtolower($error[$i]),"sql:")===false || $admin) {
                $this->globalErrors[] = $error[$i];
            }
        }
    }
}
