<?php

namespace lib\nuget;

use lib\nuget\models\NugetDependency;
use lib\nuget\models\NugetDependencyGroup;
use lib\nuget\models\NugetPackage;
use lib\utils\HttpUtils;
use lib\utils\StringUtils;
use lib\utils\XmlUtils;
use lib\utils\ZipManager;

class NugetFileParser
{
    private $properties;

    public function __construct($properties)
    {
        $this->properties = $properties;
    }

    public function loadNupkg($nupkgFile){
        $zipmanager = new ZipManager($nupkgFile);
        $files = $zipmanager->GenerateInfos();
        $nupckgName = "";
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
        $nuspecContent = $zipmanager->LoadFile($nupckgName);

        $e = $this->loadXml($nuspecContent);

        $e->TargetFramework = "";
        foreach($frameworks as $key=>$val){
            $urlKey = urldecode($key);
            if(str_contains($urlKey, "+")){
                $kk = explode("+",$urlKey);
                foreach($kk as $subk){
                    $e->TargetFramework.="|".$subk."|";
                }
            }else{
                $e->TargetFramework.="|".$key."|";
            }

        }
        $e->TargetFramework = str_replace("||","|",$e->TargetFramework);

        //$e->PackageHash = base64_encode(hash(strtolower(Settings::$PackageHash), file_get_contents($nupkgFile),true)); //true means raw, fals means in hex
        $e->PackageHash = base64_encode(hash_file(strtolower(Settings::$PackageHash), $nupkgFile,true)); //true means raw, fals mean s in hex
        $e->PackageHashAlgorithm = strtoupper(Settings::$PackageHash);
        $e->PackageSize = filesize($nupkgFile);
        $e->Listed = true;
        return $e;
    }

    /**
     * @param NugetPackage $e
     * @param mixed $m
     * @param array $xml
     * @return void
     */
    public function loadXml($nuspecContent)
    {
        $xml = XML2Array($nuspecContent);
        $e = new NugetPackage();
        $m=$xml["metadata"];
        $m = StringUtils::specialChars($m);
        $e->Version = $m["version"];
        $e->Id = $m["id"];
        $e->Title = "";
        if(array_key_exists("title",$m))$e->Title = $m["title"];
        if(StringUtils::isNullOrEmpty($e->Title)){
            $e->Title = $e->Id;
        }

        $e->IsPreRelease = NugetUtils::isPreRelease($e->Version);
        $e->Listed = true;
        $e->RequireLicenseAcceptance =false;
        $e->Description="";
        $e->Copyright="";
        if(array_key_exists("licenseurl",$m))$e->LicenseUrl = $m["licenseurl"];
        if(array_key_exists("releasenotes",$m))$e->ReleaseNotes = $m["releasenotes"];
        if(array_key_exists("iconurl",$m))$e->IconUrl = $m["iconurl"];
        else $e->IconUrl = HttpUtils::currentUrl("content/packagedefaulticon-50x50.png",$this->properties);
        if(array_key_exists("projecturl",$m))$e->ProjectUrl = $m["projecturl"];
        if(array_key_exists("requirelicenseacceptance",$m))$e->RequireLicenseAcceptance = $m["requirelicenseacceptance"];
        if(array_key_exists("description",$m))$e->Description = $m["description"];
        if(array_key_exists("tags",$m))$e->Tags = $m["tags"];
        if(array_key_exists("author",$m))$e->Author = explode(";",$m["author"]);
        if(array_key_exists("authors",$m))$e->Author = explode(";",$m["authors"]);
        if(array_key_exists("summary",$m))$e->Summary = $m["summary"];
        $e->Published = StringUtils::timeToIso8601Date();
        if(array_key_exists("copyright",$m))$e->Copyright = $m["copyright"];
        else if(array_key_exists("owners",$m))$e->Copyright = $m["owners"];
        if(array_key_exists("owners",$m))$e->Owners = $m["owners"];
        $e->Dependencies = $this->loadDependencies($m);
        $e->References = $this->loadReferences($m);
        $e->IsSymbols = stripos($nuspecContent,'SymbolsPackage')!==false;
        return $e;
    }

    private function loadDependencies($m)
    {
        $toret = array();
        if(!array_key_exists("dependencies",$m))return $toret;
        $groups = XmlUtils::xml2ArrayGetKeyOrArray($m["dependencies"],"group");

        for($i=0;$i<sizeof($groups);$i++){
            $group = $groups[$i];

            $groupEntity = new NugetDependency();
            $groupEntity->IsGroup =true;
            $groupEntity->TargetFramework = $group["@attributes"]["targetframework"];
            $dependencies = XmlUtils::xml2ArrayGetKeyOrArray($group,"dependency");
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

        $dependencies = XmlUtils::xml2ArrayGetKeyOrArray($m["dependencies"],"dependency");
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

    private function loadReferences($m)
    {
        $toret = array();
        if(!array_key_exists("references",$m))return $toret;
        $refs = XmlUtils::xml2ArrayGetKeyOrArray($m["references"],"reference");

        for($i=0;$i<sizeof($refs);$i++){
            $ref = $refs[$i]["@attributes"]["file"];
            $toret[]= $ref;
        }
        return $toret;
    }
}