<?php
if(!defined('__ROOT__'))define('__ROOT__',dirname( dirname(__FILE__)));

require_once(dirname(__FILE__)."/../root.php");

require_once(__ROOT__."/settings.php");

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
    
    public function AddRow($nugetEntity,$update)
    {
        $dbInstance =  new SmallTxtDb("3.0.0.0",__MYTXTDB_USR__,__MYTXTDBROWS_USR__,__MYTXTDBROWS_USR_TYP__);
        $toInsert = array();
        $vars = explode(":|:",__MYTXTDBROWS_USR__);
        //print_r($vars);
        foreach ($vars as $column) {
            $toInsert[$column] = $nugetEntity->$column;
        }
		
		$os = new ObjectSearch();
		$os->Parse("(UserId eq '".."$nugetEntity->UserId')",$this->GetAllColumns());
		$allRows = $this->GetAllRows(999999,0,$os);
		$itemsCount = sizeof($allRows);
		
		
        $doAdd = true;
        if($itemsCount>0){
            $toInsert["Token"]=$allRows[0]["Token"];
            $toInsert["UserId"]=$allRows[0]["UserId"];
            $doAdd = false;
        }
        
        if($doAdd){
            $toInsert["Token"]=Utils::NewGuid();
			$toInsert["Id"]=Utils::NewGuid();
            $dbInstance->add_row($toInsert);
        }else{
		
		}
        $dbInstance->save();
        return true;
    }
    
    
    public function DeleteRow($nugetEntity)
    {
        $dbInstance = new SmallTxtDb("3.0.0.0",__MYTXTDB_USR__,__MYTXTDBROWS_USR__,__MYTXTDBROWS_USR_TYP__);
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
	
	public function GetByUserId($id)
    {
        $dbInstance = new SmallTxtDb("3.0.0.0",__MYTXTDB_USR__,__MYTXTDBROWS_USR__,__MYTXTDBROWS_USR_TYP__);
		$dbInstance->BuildItem= 'nugetDbUserBuilder';
        $nameOfCaptain = "";
        foreach ($dbInstance->rows as $row) {
        	if ($row['UserId'] == $id) {
        		return $dbInstance->CreateItem($row);
        	}
        }
        return null;
    }
	
	public function GetAllRows($limit=999999,$skip=0,$objectSearch=null)
    {
        $this->initialize();
        $toret = array();
        $dbInstance = new SmallTxtDb("3.0.0.0",__MYTXTDB_USR__,__MYTXTDBROWS_USR__,__MYTXTDBROWS_USR_TYP__);
		$dbInstance->BuildItem= 'nugetDbUserBuilder';
		return $dbInstance->GetAll($limit,$skip,$objectSearch);
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