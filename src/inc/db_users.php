<?php
if(!defined('__ROOT__'))define('__ROOT__',dirname( dirname(__FILE__)));

if(__DB_TYPE__==DBMYSQL){
	require_once(__ROOT__."/inc/commons/mysqldb.php");
}else{
	require_once(__ROOT__."/inc/commons/smalltxtdb.php");
}	
require_once(__ROOT__."/inc/commons/utils.php");
require_once(__ROOT__."/inc/commons/objectsearch.php");
require_once(__ROOT__."/inc/db_usersentity.php");


define('__MYTXTDB_USR__',Path::Combine(Settings::$DataRoot,"nugetdb_usrs.txt"));
define('__MYTXTDBROWS_USR__',
      "UserId:|:Name:|:Company:|:Md5Password:|:Packages:|:Enabled:|:Email:|:Token:|:Admin:|:Id");
define('__MYTXTDBROWS_USR_TYP__',
      "string:|:string:|:string:|:string:|:string:|:boolean:|:string:|:string:|:boolean:|:string");


class UserDb
{
    public function EntityName(){ return "UserEntity";}
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
	public static function RowTypes()
	{
		return SmallTxtDb::RowTypes(__MYTXTDBROWS_USR__,__MYTXTDBROWS_USR_TYP__);
	}
	
	public function Query($query=null,$limit=99999,$skip=0)
	{
		$os = null;
		if($query!=null && $query!=""){
			$os = new ObjectSearch();
			$os->Parse($query,$this->GetAllColumns());
		}
		
		$this->initialize();
        $dbInstance = new SmallTxtDb("3.0.0.0",__MYTXTDB_USR__,__MYTXTDBROWS_USR__,__MYTXTDBROWS_USR_TYP__);
		$dbInstance->BuildItem= 'nugetDbUserBuilder';
		return $dbInstance->GetAll($limit,$skip,$os);
	}
    
    public function AddRow($nugetEntity,$update)
    {
        $dbInstance =  new SmallTxtDb("3.0.0.0",__MYTXTDB_USR__,__MYTXTDBROWS_USR__,__MYTXTDBROWS_USR_TYP__);
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
            $toInsert["Token"]=Utils::NewGuid();
			$toInsert["Id"]=Utils::NewGuid();
            $dbInstance->add_row($toInsert);
        }
        $dbInstance->save();
        return true;
    }
    
    
    public function DeleteRow($nugetEntity)
    {
        $dbInstance = new SmallTxtDb("3.0.0.0",__MYTXTDB_USR__,__MYTXTDBROWS_USR__,__MYTXTDBROWS_USR_TYP__);
        $nameOfCaptain = "";
        
		$select = array('UserId'=>$nugetEntity->UserId);
        $dbInstance->delete_row($select);
        $dbInstance->save();
    }
	
	public function GetByUserId($id)
    {
        //$dbInstance = new SmallTxtDb("3.0.0.0",__MYTXTDB_USR__,__MYTXTDBROWS_USR__,__MYTXTDBROWS_USR_TYP__);
		//$dbInstance->BuildItem= 'nugetDbUserBuilder';
        
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