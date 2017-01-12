<?php
if(!defined('__ROOT__'))define('__ROOT__',dirname( dirname(__FILE__)));

require_once(__ROOT__."/inc/commons/mysqldb.php");
require_once(__ROOT__."/inc/commons/smalltxtdb.php");

if(__DB_TYPE__==DBMYSQL){
	$dbfactory = "newMySqlDb";
}else{
	$dbfactory = "newSmallTxtDb";
}
require_once(__ROOT__."/inc/commons/utils.php");
require_once(__ROOT__."/inc/commons/objectsearch.php");
require_once(__ROOT__."/inc/db_usersentity.php");


define('__MYTXTDB_USR__',Path::Combine(Settings::$DataRoot,"nugetdb_usrs.txt"));
define('__MYTXTDBROWS_USR__',
      "UserId:|:Name:|:Company:|:Md5Password:|:Packages:|:Enabled:|:Email:|:Token:|:Admin:|:Id");
define('__MYTXTDBROWS_USR_TYP__',
      "string:|:string:|:string:|:string:|:string:|:boolean:|:string:|:string:|:boolean:|:string");
define('__MYTXTDBROWS_USR_KEY__',
      "UserId");


class UserDb
{
    public function EntityName(){ return "UserEntity";}
    public function __construct() 
    {
        $this->initialize();
    }
    
    private function initialize()
    {
        
    }
	public static function RowTypes()
	{
		return SmallTxtDb::RowTypes(__MYTXTDBROWS_USR__,__MYTXTDBROWS_USR_TYP__);
	}
	
	public function Query($query=null,$limit=99999,$skip=0)
	{
		global $dbfactory;
		$os = null;
		if($query!=null && $query!=""){
			$os = new ObjectSearch();
			$os->Parse($query,$this->GetAllColumns());
		}
		
		$this->initialize();
        $dbInstance =call_user_func($dbfactory,__DB_VERSION__,__MYTXTDB_USR__,__MYTXTDBROWS_USR__,__MYTXTDBROWS_USR_TYP__,__MYTXTDBROWS_USR_KEY__);
		$dbInstance->BuildItem= 'nugetDbUserBuilder';
		return $dbInstance->GetAll($limit,$skip,$os);
	}
    
    public function AddRow($nugetEntity,$update)
    {
		global $dbfactory;
        $dbInstance =  call_user_func($dbfactory,__DB_VERSION__,__MYTXTDB_USR__,__MYTXTDBROWS_USR__,__MYTXTDBROWS_USR_TYP__,__MYTXTDBROWS_USR_KEY__);
        $toInsert = array();
        $vars = explode(":|:",__MYTXTDBROWS_USR__);
        //print_r($vars);
        foreach ($vars as $column) {
            $toInsert[$column] = $nugetEntity->$column;
        }
        $doAdd = true;
		$foundedUsers = $this->Query("(UserId eq '".$nugetEntity->UserId."')",1,0);
		if(sizeof($foundedUsers)==1){
			$toInsert["Token"]=$foundedUsers[0]->Token;
            $toInsert["UserId"]=$foundedUsers[0]->UserId;
			if($update){
				$doAdd = false;
			}else{
				throw new Exception("Duplicate found!");
			}
		}
		
        if($doAdd){
            $toInsert["Token"]=Utils::NewGuid();
			$toInsert["Id"]=Utils::NewGuid();
            $dbInstance->add_row($toInsert);
        }else{
			$dbInstance->update_row($toInsert,array("UserId"=>$toInsert["UserId"]));
		}
		
        $dbInstance->save();
        return true;
    }
    
    
    public function DeleteRow($nugetEntity)
    {
		global $dbfactory;
        $dbInstance = call_user_func($dbfactory,__DB_VERSION__,__MYTXTDB_USR__,__MYTXTDBROWS_USR__,__MYTXTDBROWS_USR_TYP__,__MYTXTDBROWS_USR_KEY__);
        $nameOfCaptain = "";
        
		$select = array('UserId'=>$nugetEntity->UserId);
        $dbInstance->delete_row($select);
        $dbInstance->save();
    }
	
	public function GetByUserId($id)
    {
		$items = $this->Query("(UserId eq '".$id."')",1,0);
		if(sizeof($items)==1) return $items[0];
        
        return null;
    }
	
	
    
    public function GetAllColumns()
    {
        return explode(":|:",__MYTXTDBROWS_USR__);
    }
}

function nugetDbUserBuilder()
{
return new UserEntity();
}
?>