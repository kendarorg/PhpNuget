<?php

require_once(dirname(__FILE__)."/../root.php");
require_once(__ROOT__."/settings.php");

require_once(__ROOT__."/inc/commons/mysqldb.php");
require_once(__ROOT__."/inc/commons/smalltxtdb.php");

if(__DB_TYPE__==DBMYSQL){
	$dbfactory = "newMySqlDb";
}else{
	$dbfactory = "newSmallTxtDb";
}	
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/objectsearch.php");
require_once(__ROOT__."/inc/db_nugetpackagesentity.php");

define('__MYTXTDB_PKG__',Path::Combine(Settings::$DataRoot,"nugetdb_pkg.txt"));
define('__MYTXTDBROWS_PKG__',
      "Version0:|:Version1:|:Version2:|:Version3:|:VersionBeta:|:Version:|:Title:|:Id:|:Author:|:IconUrl:|:LicenseUrl:|:ProjectUrl:|:DownloadCount:|:".
      "RequireLicenseAcceptance:|:Description:|:ReleaseNotes:|:Published:|:Dependencies:|:".
      "PackageHash:|:PackageHashAlgorithm:|:PackageSize:|:Copyright:|:Tags:|:IsAbsoluteLatestVersion:|:".
      "IsLatestVersion:|:Listed:|:VersionDownloadCount:|:References:|:TargetFramework:|:Summary:|:IsPreRelease:|:Owners:|:UserId");
define('__MYTXTDBROWS_PKG_TYPES__',
      "number:|:number:|:number:|:number:|:string:|:string:|:string:|:string:|:object:|:string:|:string:|:string:|:number:|:".
      "boolean:|:string:|:string:|:date:|:object:|:".
      "string:|:string:|:number:|:string:|:string:|:boolean:|:".
      "boolean:|:boolean:|:number:|:array:|:string:|:string:|:boolean:|:string:|:string");      
define('__MYTXTDBROWS_PKG_EDITABLE__',
      "Tags:|:IsAbsoluteLatestVersion:|:Title:|:Title:|:IconUrl:|:LicenseUrl:|:ProjectUrl:|:".
      "RequireLicenseAcceptance:|:Description:|:ReleaseNotes:|:Copyright:|:".
      "IsLatestVersion:|:Listed:|:TargetFramework:|:Summary:|:IsPreRelease:|:Owners:|:UserId");  
define('__MYTXTDBROWS_PKG_KEY__',
      "Id:|:Version");	  

function nugetDbPackageBuilder()
{
return new PackageDescriptor();
}	 
class NuGetDb
{
	public function EntityName(){ return "PackageDescriptor";}

    public function __constructor()
    {
        $this->initialize();
    }
    
    private function initialize()
    {
        
    }
	
	public function Query($query=null,$limit=99999,$skip=0)
	{
		global $dbfactory;
		$os = null;
		if($query!=null && $query!=""){
			$os = new PhpNugetObjectSearch();
			$os->Parse($query,$this->GetAllColumns());
		}
		$this->initialize();
        $dbInstance = call_user_func($dbfactory,__DB_VERSION__,__MYTXTDB_PKG__,__MYTXTDBROWS_PKG__,__MYTXTDBROWS_PKG_TYPES__,__MYTXTDBROWS_PKG_KEY__);
		$dbInstance->BuildItem= 'nugetDbPackageBuilder';
		$res =  $dbInstance->GetAll($limit,$skip,$os);
		foreach($res as $row){
			if(ends_with(strtolower($row->IconUrl),strtolower("packagedefaulticon-50x50.png"))){
				$row->IconUrl = UrlUtils::CurrentUrl(Settings::$SiteRoot."content/packagedefaulticon-50x50.png");
			}
		}
		return $res;
	}
	
	public static function RowTypes()
	{
		return SmallTxtDb::RowTypes(__MYTXTDBROWS_PKG__,__MYTXTDBROWS_PKG_TYPES__);
	}
	
    public function AddRow($nugetEntity,$update)
    {
		if($nugetEntity->Id=="" || $nugetEntity->Version==""){
			throw new Exception("Missing Id and/or Version");
		}
		global $dbfactory;
        $dbInstance =  call_user_func($dbfactory,__DB_VERSION__,__MYTXTDB_PKG__,__MYTXTDBROWS_PKG__,__MYTXTDBROWS_PKG_TYPES__,__MYTXTDBROWS_PKG_KEY__);
		$dbInstance->BuildItem= 'nugetDbPackageBuilder';
        $toInsert = array();
        $vars = explode(":|:",__MYTXTDBROWS_PKG__);
        //print_r($vars);
		$v = buildSplitVersion($nugetEntity->Version);
		$nugetEntity->Version0 = $v[0];
		$nugetEntity->Version1 = $v[1];
		$nugetEntity->Version2 = $v[2];
		$nugetEntity->Version3 = $v[3];
		$nugetEntity->VersionBeta = $v[4];
		
		if($nugetEntity->TargetFramework==null){
			$nugetEntity->TargetFramework = "";
		}
		if($nugetEntity->ProjectUrl==null){
			$nugetEntity->ProjectUrl = "";
		}
		if($nugetEntity->LicenseUrl==null){
			$nugetEntity->LicenseUrl = "";
		}
		if($nugetEntity->Tags==null){
			$nugetEntity->Tags = "";
		}
		if($nugetEntity->Summary==null){
			$nugetEntity->Summary = "";
		}
		if($nugetEntity->Copyright==null){
			$nugetEntity->Copyright = "";
		}
		if($nugetEntity->Dependencies==null){
			$nugetEntity->Dependencies = "";
		}
		if($nugetEntity->Description==null){
			$nugetEntity->Description = "";
		}
		if($nugetEntity->IconUrl==null){
			$nugetEntity->IconUrl = "";
		}
		if($nugetEntity->Author==null){
			$nugetEntity->Author = "";
		}
		if($nugetEntity->Title==null){
			$nugetEntity->Id = "";
		}
		if($nugetEntity->DownloadCount==null){
			$nugetEntity->DownloadCount = 0;
		}
		if($nugetEntity->VersionDownloadCount==null){
			$nugetEntity->VersionDownloadCount = 0;
		}
		
        foreach ($vars as $column) {
			if(property_exists($nugetEntity,$column)){
				$toInsert[$column] = $nugetEntity->$column;
			}
        }
        $doAdd = true;
        
		$foundedUsers = $this->Query("(Version eq '".$nugetEntity->Version."') and (Id eq '".$nugetEntity->Id."')",1,0);
		if(sizeof($foundedUsers)==1){
			if($update){
				$doAdd = false;
			}else{
				throw new Exception("Duplicate found!");
			}
		}
		
		
        if($doAdd)$dbInstance->add_row($toInsert);
		else $dbInstance->update_row($toInsert,array("Id"=>$toInsert["Id"],"Version"=>$toInsert["Version"]));
        $dbInstance->save();
        return true;
    }
    
    public function DeleteRow($nugetEntity)
    {
		global $dbfactory;
        $dbInstance = call_user_func($dbfactory,__DB_VERSION__,__MYTXTDB_PKG__,__MYTXTDBROWS_PKG__,__MYTXTDBROWS_PKG_TYPES__,__MYTXTDBROWS_PKG_KEY__);
		$dbInstance->BuildItem= 'nugetDbPackageBuilder';
        $nameOfCaptain = "";
        
		$select = array('Id'=>$nugetEntity->Id,'Version'=>$nugetEntity->Version);
        $dbInstance->delete_row($select);
        $dbInstance->save();
    }
    
    
    
    public function GetAllColumns()
    {
        return explode(":|:",__MYTXTDBROWS_PKG__);
    }
}
?>