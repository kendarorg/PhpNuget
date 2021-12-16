<?php

namespace lib\rest\utils;

use lib\rest\NetVersionHelper;

class NugetResultParser
{
    /**
     * @var ResourcesLoader
     */
    private $resourcesLoader;
    /**
     * @var array|false|string|string[]
     */
    private $entryTemplate;

    /**
     * @param ResourcesLoader $resourcesLoader
     */
    public function __construct($resourcesLoader)
    {
        $this->resourcesLoader = $resourcesLoader;
    }

    public function parse(NugetQueryResult $result, $lastQuery)
    {
        $query = $result->query;
        $pg = $query->pagination;
        $allRows = $result->data;

        $r = array();
        $r["@BASEURL@"] = $query->baseUrl;
        $r["@NEXTITEM@"] = "";
        $r["@ITEMSCOUNT@"] = "";

        if ($query->itemsCount >= 0) {
            $r["@ITEMSCOUNT@"] = "<m:count>" . $query->itemsCount . "</m:count>";
        }

        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        echo $this->resourcesLoader->getResource($r, "entrytemplatepre.xml");
        $this->entryTemplate = $this->resourcesLoader->getResource(array(), "entrytemplate.xml");

        if ($query->setupLatest) {
            for ($i = sizeof($allRows) - 1; $i >= 0; $i--) {
                $allRows[$i]->IsAbsoluteLatestVersion = false;
                $allRows[$i]->IsLatestVersion = false;
            }
            if (sizeof($allRows) > 0) {
                $allRows[sizeof($allRows) - 1]->IsAbsoluteLatestVersion = true;
            }
            for ($i = sizeof($allRows) - 1; $i >= 0; $i--) {
                $row = $allRows[$i];
                if ($row->IsPreRelease != true) {
                    $row->IsLatestVersion = true;
                    break;
                }
            }
        }

        for ($i = 0; $i < sizeof($allRows) && $i < $pg->Top; $i++) {
            $row = $allRows[$i];

            echo $this->buildNuspecEntity($query->baseUrl, $row);
        }


        if (sizeof($allRows) >= $pg->Top) {
            $act = $query->xmlAction;
            $nq = $this->setupLastQuery($lastQuery);
            if ($act != "Search") {
                $nq = "\$skip=" . ($pg->Skip + $pg->Top) . "&amp;\$top=" . $pg->Top . $nq;
                echo "<link rel=\"next\" href=\"" . $query->baseUrl . "/api/" .
                    $this->resourcesLoader->version . "/" . $act . "?" . $nq . "\"/>";
            }
            /*?><link rel="next" href="http://localhost:8020/phpnuget/api/v2/Search?$skip=30&amp;$top=30&amp;searchTerm=''&amp;$filter=IsAbsoluteLatestVersion&amp;$orderby=DownloadCount+desc%2CId"/><?php*/
        }

        echo $this->resourcesLoader->getResource($r, "entrytemplatepost.xml");
    }

    public function buildNuspecEntity($baseUrl, $e)
    {
        $t = $this->entryTemplate;
        $t .= "  ";
        if ($e->Author == null) {
            $e->Author = "";
        }
        $authors = explode(";", $e->Author);
        $author = "";
        if (sizeof($authors) > 0) {
            for ($i = 0; $i < sizeof($authors); $i++) {
                $authors[$i] = htmlspecialchars($authors[$i]);
            }
            $author = "<name>" . implode("</name>\n<name>", $authors) . "</name>";
        }
        //print_r($e);
        $baseUrl = trim($baseUrl, "\\/");

        $t = $this->safeReplace("\${BASEURL}", $baseUrl, $t);
        $t = $this->safeReplace("\${NUSPEC.ID}", $e->Id, $t);


        $t = $this->safeReplace("\${NUSPEC.IDLOWER}", strtolower($e->Id), $t);
        $t = $this->safeReplace("\${NUSPEC.TITLE}", $e->Title, $t, true);
        $t = $this->safeReplace("\${NUSPEC.VERSION}", $e->Version, $t);
        $t = $this->safeReplace("\${NUSPEC.LICENSEURL}", $e->LicenseUrl, $t, true);
        $t = $this->safeReplace("\${NUSPEC.PROJECTURL}", $e->ProjectUrl, $t, true);
        $t = $this->safeReplace("\${NUSPEC.REQUIRELICENSEACCEPTANCE}", $e->RequireLicenseAcceptance ? "true" : "false", $t);
        $t = $this->safeReplace("\${NUSPEC.DESCRIPTION}", $e->Description, $t, true);
        $t = $this->safeReplace("\${NUSPEC.TAGS}", $e->Tags, $t, true);
        $t = $this->safeReplace("\${NUSPEC.SUMMARY}", $e->Summary, $t, true);
        $t = $this->safeReplace("\${NUSPEC.RELEASENOTES}", $e->ReleaseNotes, $t, true);

        $t = $this->safeReplace("\${NUSPEC.AUTHOR}", $author, $t);
        $t = $this->safeReplace("\${NUSPEC.AUTHORS}", $author, $t);
        $t = $this->safeReplace("\${DB.PUBLISHED}", $e->Published, $t);
        $t = $this->safeReplace("\${DB.PACKAGESIZE}", $e->PackageSize, $t);
        $t = $this->safeReplace("\${DB.PACKAGEHASHALGORITHM}", $e->PackageHashAlgorithm, $t);
        $t = $this->safeReplace("\${DB.PACKAGEHASH}", $e->PackageHash, $t);

        if (is_string($e->Dependencies) && strlen($e->Dependencies) == 0) {
            $t = str_replace("\${NUSPEC.DEPENDENCIES}", "", $t);
        } else if (is_array($e->Dependencies)) {
            $t = str_replace("\${NUSPEC.DEPENDENCIES}", $this->makeDepString($e->Dependencies), $t);
        }
        $t = $this->safeReplace("\${DB.DOWNLOADCOUNT}", $e->DownloadCount, $t);
        $t = $this->safeReplace("\${DB.UPDATED}", $e->Published, $t);


        $t = $this->safeReplace("\${DB.ISPRERELEASE}", $e->IsPreRelease ? "true" : "false", $t);
        $t = $this->safeReplace("\${DB.ISABSOLUTELATESTVERSION}", $e->IsAbsoluteLatestVersion ? "true" : "false", $t);
        $t = $this->safeReplace("\${DB.ISLATESTVERSION}", $e->IsLatestVersion ? "true" : "false", $t);
        $t = $this->safeReplace("\${DB.VERSIONDOWNLOADCOUNT}", "-1", $t);
        $t = $this->safeReplace("\${DB.LISTED}", $e->Listed ? "true" : "false", $t);
        $t = $this->safeReplace("\${DB.COPYRIGHT}", $e->Copyright, $t, true);

        return preg_replace('/<!--(.*)-->/Uis', '', $t);
    }

    private function safeReplace($what, $with, $in, $useSpecialChars = false, $glue = " ")
    {
        if (null == $with) {
            $with = "";
        }
        if (is_string($with)) {
            if ($useSpecialChars) {
                return str_replace($what, htmlspecialchars($with), $in);
            } else {
                return str_replace($what, $with, $in);
            }
        } else if (is_array($with)) {
            if ($useSpecialChars) {
                return str_replace($what, htmlspecialchars(implode($glue, $with)), $in);
            } else {
                return str_replace($what, implode($glue, $with), $in);
            }
        } else {
            return str_replace($what, $with, $in);
        }
    }

    private function makeDepString($d)
    {
        $tora = array();

        //<d:Dependencies>Castle.Core:3.1.0:net40|Castle.Windsor:3.1.0:net40|Common.Logging:2.0.0:net40|Quartz:2.0.1:net40|Castle.Core:2.1.0:net20|Castle.Windsor:2.1.0:net20|Common.Logging:1.0.0:net20|Quartz:1.0.1:net20</d:Dependencies>
        if(is_array($d)){
            for($i=0;$i<sizeof($d);$i++){
                $sd = $d[$i];
                if($sd->IsGroup){
                    $fw= NetVersionHelper::translateNetVersion($sd->TargetFramework);
                    //if(strpos($fw,"+")===FALSE) {
                    for($j=0;$j<sizeof($sd->Dependencies);$j++){
                        $sdd = $sd->Dependencies[$j];
                        $tora[]=($sdd->Id.":".$sdd->Version.":".$fw);
                    }
                    /*}else{
                        $fws = explode("+",$fw);
                        for($k=0;$k<sizeof($fws);$k++){
                            $subfw = $fws[$k]
                            for($j=0;$j<sizeof($sd->Dependencies);$j++){
                                $sdd = $sd->Dependencies[$j];
                                $tora[]=($sdd->Id.":".$sdd->Version.":".$subfw);
                            }
                        }
                    }*/
                }else{
                    $tora[]=($sd->Id.":".$sd->Version.":");
                }
            }
        }
        //print_r($tora);die();
        return implode("|",$tora);
    }

    /**
     * @param NugetQuery $query
     * @return void
     */
    private function setupLastQuery( $lastQuery)
    {
        $res = "";
        foreach($lastQuery as $k=>$v){
            $res.="&amp;".$k."=".htmlentities($v);
        }
        return $res;
    }
}