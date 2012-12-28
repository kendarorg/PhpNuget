<?php
define('__ROOT__',dirname( dirname(__FILE__)));
require_once(__ROOT__.'/settings.php'); 
require_once(__ROOT__."/inc/mytxtdb.php");
require_once(__ROOT__."/inc/userentity.php");
require_once(__ROOT__.'/inc/utils.php'); 

define('__MYTXTDB_USR__',__ROOT__."/db/nugetdb_usrs.txt");
define('__MYTXTDBROWS_USR__',
      "UserId:|:Name:|:Company:|:Md5Password:|:Packages:|:Enabled:|:Email:|:Token:|:Admin");


class UserDb
{
    public function __construct() 
    {
        $this->initialize();
    }
    
    public function UserDbSortUserId()
    {
        $this->initialize();
    }
    
    private function initialize()
    {
        
        
    }
    
    public function AddRow($nugetEntity,$update)
    {
        $dbInstance =  new SmallTxtDb(__MYTXTDB_USR__,__MYTXTDBROWS_USR__);
        $toInsert = array();
        $vars = explode(":|:",__MYTXTDBROWS_USR__);
        //print_r($vars);
        foreach ($vars as $column) {
            $toInsert[$column] = $nugetEntity->$column;
        }
        $doAdd = true;
        for($i=0;$i<sizeof($dbInstance->rows);$i++){
            if($dbInstance->rows[$i]["UserId"]==$nugetEntity->UserId){
                 if($update){
                    $toInsert["Token"]=$dbInstance->rows[$i]["Token"];
                    $toInsert["UserId"]=$dbInstance->rows[$i]["UserId"];
                    $dbInstance->rows[$i] = $toInsert;
                    $doAdd = false;
                 }
            }
        }
        
        if($doAdd){
            $toInsert["Token"]=getGUID();
            $dbInstance->add_row($toInsert);
        }
        $dbInstance->save();
        return true;
    }
    
    
    public function DeleteRow($nugetEntity)
    {
        $dbInstance = new SmallTxtDb(__MYTXTDB_USR__,__MYTXTDBROWS_USR__);
        $nameOfCaptain = "";
        $rowNumber = 0;
        foreach ($dbInstance->rows as $row) {
        	if ($row['UserId'] == $nugetEntity->UserId) {
        		$dbInstance->delete_row($rowNumber);
        		break;
        	}
        	$rowNumber++;
        }
        $dbInstance->save();
    }
    
    public function GetAllRows()
    {
        $this->initialize();
        $toret = array();
        $dbInstance = new SmallTxtDb(__MYTXTDB_USR__,__MYTXTDBROWS_USR__);
        foreach( $dbInstance->rows as $row){
            $e = new UserEntity();
            foreach ($row as $key=> $value) {
                $e->$key = $value;
                
            }
            $toret[]=$e;
        }
        return $toret;
    }
    
    public function GetAllColumns()
    {
        return split(":|:",__MYTXTDBROWS_USR__);
    }
}
?>