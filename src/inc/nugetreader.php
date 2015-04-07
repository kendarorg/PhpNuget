<?php
require_once(dirname(__FILE__)."/../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/db_nugetpackagesentity.php");
require_once(__ROOT__."/inc/commons/zipmanager.php");
require_once(__ROOT__."/inc/db_nugetpackages.php");
require_once(__ROOT__."/inc/commons/utils.php");
require_once(__ROOT__."/inc/commons/xmlutils.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/phpnugetobjectsearch.php");
	
	
define('__TEMPLATE_FILE__',__ROOT__."/inc/nugettemplate.xml");

/*

The comparison function must return an integer less than, equal to, or greater than zero if 
the first argument is considered to be respectively less than, equal to, or greater than the second. 

Sort(a,b) <0   => a<b
Sort(a,b) >0   => a>b
Sort(a,b) =0   => a=b
*/
function NugetManagerSortIdVersion($a, $b)
{
    $res = strcmp($a->Id, $b->Id);
    if($res==0){
       $aVersion = explode(".",$a->Version);
       $bVersion = explode(".",$b->Version);
       for($i=0;$i<sizeof($aVersion) && $i<sizeof($bVersion);$i++){
            $res = $aVersion[$i]-$bVersion[$i];
            if($res!=0) return $res; 
       }
    }
    return $res;
}

/*
Sort(a,b) <0   => a<b
Sort(a,b) >0   => a>b
Sort(a,b) =0   => a=b
*/
function NugetManagerSortVersion($a, $b)
{
    $res = 0;
    $aVersion = explode(".",$a);
    $bVersion = explode(".",$b);
    for($i=0;$i<sizeof($aVersion) && $i<sizeof($bVersion);$i++){
        $res = $aVersion[$i]-$bVersion[$i];
        if($res!=0) return $res; 
    }
    return $res; 
}

//http://net.tutsplus.com/articles/news/how-to-open-zip-files-with-php/
class NugetManager
{
    var $template = null;
    
    public function DeleteNuspecData($e)
    {
        $nugetDb = new NuGetDb();
        
        
        $destination = Path::Combine(Settings::$PackagesRoot,"/".strtolower($e->Id).".".strtolower($e->Version).".nupkg");
        
        if(file_exists($destination)) unlink($destination);
        
        $nugetDb->DeleteRow($e);
        
    }
    
    public function SpecialChars($hasMap)
    {
        foreach($hasMap as $key=>$value){
			if(!is_array($value)){
				$hasMap[$key]=trim(htmlspecialchars($value));
			}else{
				//TODO: Special chars deep 
				$hasMap[$key] = $value;
				//$hasMap[$key] = array();
				//for($i=0;$i<sizeof($value);$i++){
					//$hasMap[$key][] = htmlspecialchars($value[$i]);
				//}
			}
	    }
    }
	
	public function LoadXml($e,$m,$xml)
	{
	    $this->SpecialChars($m);
		$e->Version = $m["version"];
        $e->Id = $m["id"];
        if(array_key_exists("title",$m))$e->Title = $m["title"];
        if(sizeof($e->Title)==0 || $e->Title==""){
            $e->Title = $e->Id;   
        }
		if(sizeof($e->Id)==0 || $e->Id==""){
            $e->Id = $e->Title;   
        }
		$e->IsPreRelease = PhpNugetObjectSearch::IsPreRelease($e->Version);
		$e->Listed = true;
        if(array_key_exists("licenseurl",$m))$e->LicenseUrl = $m["licenseurl"];
		if(array_key_exists("iconurl",$m))$e->IconUrl = $m["iconurl"];
		else $e->IconUrl = UrlUtils::CurrentUrl(Settings::$SiteRoot."content/packagedefaulticon-50x50.png");
        if(array_key_exists("projecturl",$m))$e->ProjectUrl = $m["projecturl"];
        $e->RequireLicenseAcceptance = $m["requirelicenseacceptance"];
        $e->Description = $m["description"];
		if(array_key_exists("tags",$m))$e->Tags = $m["tags"];
        if(array_key_exists("author",$m))$e->Author = $m["author"];
		if(array_key_exists("authors",$m))$e->Author = $m["authors"];
		if(array_key_exists("summary",$m))$e->Summary = $m["summary"];
        $e->Published = Utils::FormatToIso8601Date();
        if(array_key_exists("copyright",$m))$e->Copyright = $m["copyright"];
		else $e->Copyright = $m["owners"];
        if(array_key_exists("owners",$m))$e->Owners = $m["owners"];
	}
    
	public function LoadNuspecFromFile($nupkgFile)
	{
		$zipmanager = new ZipManager($nupkgFile);
        $files = $zipmanager->GenerateInfos();
        $nupckgName = "";
        foreach($files["entries_name"] as $fileName)
        {
            $pinfo = pathinfo($fileName);
            if($pinfo["basename"]==$fileName){
                if(ends_with($fileName,".nuspec")){
                    $nupckgName = $fileName;
                }
            }
        }
        $nuspecContent = $zipmanager->LoadFile($nupckgName);
        
		
        $xml = XML2Array($nuspecContent);
        $e = new PackageDescriptor();
        $m=$xml["metadata"];
        
        $this->LoadXml($e,$m,$xml);
        /*for($i=0;$i<sizeof($ark);$i++){
            $m[strtolower ($ark[$i])]=$mt[$ark[$i]];
        }*/
        
        $e->Dependencies = $this->LoadDependencies($m);
        
        
        $e->References = $this->LoadReferences($m);
       
        $e->PackageHash = base64_encode(hash(strtolower(Settings::$PackageHash), file_get_contents($nupkgFile),true)); //true means raw, fals means in hex
        $e->PackageHashAlgorithm = strtoupper(Settings::$PackageHash);
        $e->PackageSize = filesize($nupkgFile);
        $e->Listed = true;
		return $e;
	}
	
    public function SaveNuspec($nupkgFile,$e)
    {
		global $loginController;
		$nugetDb = new NuGetDb();
		$os = new PhpNugetObjectSearch();
		$query = "Id eq '".$e->Id."' orderby Version desc";
		$os->Parse($query,$nugetDb->GetAllColumns());
		$res = $nugetDb->GetAllRows(999999,0,$os);
		if(sizeof($res)>0 && !$loginController->Admin){
			$id = $res[0]->UserId;
			if($id!=$e->UserId){
				throw new Exception("Unauthorized!");
			}
		}else if(sizeof($res)>0 && $loginController->Admin){
			$e->UserId = $res[0]->UserId;
		}
		$e->IsPreRelease = indexOf($e->Version,"-")>0;
		if($nugetDb->AddRow($e,false)){
			$destination =Path::Combine(Settings::$PackagesRoot,strtolower($e->Id).".".strtolower($e->Version).".nupkg");
			if(strtolower($nupkgFile)!=strtolower($destination)){
				if(file_exists($destination)){
					unlink($destination);
				}
				
				rename($nupkgFile,$destination);
			}
		}else{
			if(strtlower($nupkgFile)!=strtlower($destination)){
				if(file_exists($nupkgFile)){
					unlink($nupkgFile);
				}
			}
		}
    }
    
    public function LoadAllPackagesEntries()
    {
        $nugetDb = new NuGetDb();
        $toret = $nugetDb->GetAllRows();
        
        return $toret;
    }
    
    
    public function IsValid($e,$c,$isPackagesById)
    {
        if($isPackagesById) return $e->Id==$c;
        if(stripos($e->Title,$c)!==false) return true;
        if(stripos($e->Description,$c)!==false) return true;
        if(stripos($e->Tag,$c)!==false) return true;

        if(stripos($e->Id,$c)!==false) return true;
        return false;              
    }  
  
    private function LoadDependencies($m)
    {
         
        $toret = array();
        if(!array_key_exists("dependencies",$m))return $toret;
        $groups = XML2ArrayGetKeyOrArray($m["dependencies"],"group");
        
        for($i=0;$i<sizeof($groups);$i++){
            $group = $groups[$i];
            
            $groupEntity = new NugetDependencyGroup();
            $groupEntity->TargetFramework = $group["@attributes"]["targetframework"];
            $dependencies = XML2ArrayGetKeyOrArray($group,"dependency");
            $groupEntity->Dependencies = array();
            for($a=0;$a<sizeof($dependencies);$a++){
                $dependency = $dependencies[$a];
                
                $dep = new NugetDependency();
                $dep->Id = $dependency["@attributes"]["id"];
                $dep->Version = $dependency["@attributes"]["version"];
                $groupEntity->Dependencies[] = $dep;
                   
            }
            
            $toret[]=$groupEntity;
        }
        
        $dependencies = XML2ArrayGetKeyOrArray($m["dependencies"],"dependency");
        for($a=0;$a<sizeof($dependencies);$a++){
            $dependency = $dependencies[$a];
            
            $dep = new NugetDependency();
            $dep->Id = $dependency["@attributes"]["id"];
            if(array_key_exists("version",$dependency["@attributes"])){
				$dep->Version = $dependency["@attributes"]["version"];
			}else{
				$dep->Version = "0.0.0.0";
			}
            $toret[] = $dep;
               
        }
        
        return $toret;
    }
    
    private function LoadReferences($m)
    {
        $toret = array();
        if(!array_key_exists("references",$m))return $toret;
        $refs = XML2ArrayGetKeyOrArray($m["references"],"reference");
       
        for($i=0;$i<sizeof($refs);$i++){
            $ref = $refs[$i]["@attributes"]["file"];
            $toret[]= $ref;
        }
        return $toret;
    }
    
    
    private function TranslateNet($tf)
    {
        $tf = strtolower($tf);
        switch($tf){
            case(".netframework3.5"): return "net35";
            case(".netframework4.0"): return "net40";
            case(".netframework3.0"): return "net30";
            case(".netframework2.0"): return "net20";
            case(".netframework1.0"): return "net10";
            default: return "UNKNOWN";
        }
    }
    
    public function LoadNextVersions($packages,$versions,$available)
    {
        $result = array();
        for($i=0;$i< sizeof($available);$i++){
            $sd = $available[$i];
            $packageFounded =false;
            for($j=0;$j< sizeof($packages) && $packageFounded==false;$j++){
                $sp = $packages[$j];
                $vp = $versions[$j];
                if($sd->Id == $sp){
                    //echo $sd->Version." XXX ".$vp." res ".NugetManagerSortIdVersion($sd->Version,$vp)."\n";
                    if(NugetManagerSortVersion($sd->Version,$vp)>0){
                        $packageFounded=true;
                    }
                }
            }
            if($packageFounded){
               $result[]=$sd; 
            }
            
        }
        //echo "AAAA".sizeof($result); die();
        return $result;
    }
    
}
?>