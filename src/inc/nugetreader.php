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
		if(array_key_exists("releasenotes",$m))$e->ReleaseNotes = $m["releasenotes"];
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
        //uplogh("nugetreader","Nupckg content of '".$nupkgFile."'!",$files);
        $frameworks = array();
        foreach($files["entries_name"] as $fileName)
        {
            $pinfo = pathinfo($fileName);
            if($pinfo["basename"]==$fileName && $nupckgName==""){
                if(ends_with($fileName,".nuspec")){
                    $nupckgName = $fileName;
                }
            }
            
            $isLib= strpos($pinfo["dirname"],"lib/");
            if($isLib!==false && $isLib ==0){
            	$libex = explode("/",$pinfo["dirname"]);
            	if(sizeof($libex)>=2){
            		$frameworks[$libex[1]]=$libex[1];
            	}
            }
        }
        uplogv("nugetreader.nuget","ZIPCONT",$files);
        $nuspecContent = $zipmanager->LoadFile($nupckgName);
        
		//uplogv("nugetreader","Nuspec content!",$nuspecContent);
        $xml = XML2Array($nuspecContent);
        $e = new PackageDescriptor();
        $m=$xml["metadata"];
        
        $this->LoadXml($e,$m,$xml);
        /*for($i=0;$i<sizeof($ark);$i++){
            $m[strtolower ($ark[$i])]=$mt[$ark[$i]];
        }*/
        $e->TargetFramework = "";
        
        uplogv("nugetreader.nuget","fwks",$frameworks);
        foreach($frameworks as $key=>$val){
        	$urlKey = urldecode($key);
        	if(strpos($urlKey,"+")!==false){
        		$kk = explode("+",$urlKey);
        		foreach($kk as $subk){
        			$e->TargetFramework.="|".$subk."|";
        		}
        	}else{
        		$e->TargetFramework.="|".$key."|";
        	}
        	
        }
        $e->TargetFramework = str_replace("||","|",$e->TargetFramework);
        $e->Dependencies = $this->LoadDependencies($m);
        
        
        $e->References = $this->LoadReferences($m);
       
        //$e->PackageHash = base64_encode(hash(strtolower(Settings::$PackageHash), file_get_contents($nupkgFile),true)); //true means raw, fals means in hex
		$e->PackageHash = base64_encode(hash_file(strtolower(Settings::$PackageHash), $nupkgFile,true)); //true means raw, fals mean s in hex
        $e->PackageHashAlgorithm = strtoupper(Settings::$PackageHash);
        $e->PackageSize = filesize($nupkgFile);
        $e->Listed = true;
        uplogv("nugetreader.nuget","nuspec",$e);
		return $e;
	}
	
    public function SaveNuspec($nupkgFile,$e)
    {
		global $loginController;
		$nugetDb = new NuGetDb();
		$query = "Id eq '".$e->Id."' orderby Version desc";
		$res = $nugetDb->Query($query,999999,0);
		if(sizeof($res)>0 && !$loginController->Admin){
			$id = $res[0]->UserId;
			if($id!=$e->UserId){
				throw new Exception("Unauthorized!");
			}
		}else if(sizeof($res)>0 && $loginController->Admin){
			$e->UserId = $res[0]->UserId;
		}
		$e->IsPreRelease = indexOf($e->Version,"-")>0;
		if($nugetDb->AddRow($e,__ALLOWPACKAGEUPDATE__)){
			$destination =Path::Combine(Settings::$PackagesRoot,($e->Id).".".($e->Version).".nupkg");
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
        return $nugetDb->Query();
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
    
    
    /*private function TranslateNet($tf)
    {
        return translateNetVersion($tf);
    }*/
    
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