<?php

namespace lib\rest;

use lib\http\BaseHandler;

class BaseQueryable extends BaseHandler
{
    private $apiVersion;

    public function __construct($apiVersion, $properties)
    {
        parent::__construct($properties);
        $this->apiVersion = $apiVersion;
    }

    protected function _append($query,$data,$linkWith="")
    {
        if(strlen($query)==0) return $data;
        return $query." ".$linkWith." ".$data;
    }

    protected function _getPagination($request,$top=10,$verbs = "all")
    {
        $pg = new Pagination();
        $pg->Skip = $request->getParam("\$skip",0,$verbs);
        $pg->Top = $request->getParam("\$top",$top,$verbs);
        return $pg;
    }

    protected function _query($request,$query,$setupLatest = false)
    {
        $pg= $this->_getPagination($request);
        $db = new NuGetDb();

        $count = $request->getBoolean("count",false);
        $allpages = $request->getParam("\$inlinecount","none")=="allpages";
        $itemsCount = -1;

        if($count || $allpages){
            $allRows = $db->Query($query);
            $itemsCount = sizeof($allRows);
            if(!$allpages){
                HttpUtils::WriteData($itemsCount);
            }
        }
        $allRows = $db->Query($query,$pg->Top+1,$pg->Skip);


        if(!UrlUtils::IsFake()){
            header('Content-Type: 	application/atom+xml;type=feed;charset=utf-8');
        }
        $baseUrl = UrlUtils::CurrentUrl(Settings::$SiteRoot);

        //
        $r = array();
        $r["@BASEURL@"]=$baseUrl;
        $r["@NEXTITEM@"]="";
        $r["@ITEMSCOUNT@"]="";

        if($itemsCount>=0){
            $r["@ITEMSCOUNT@"]="<m:count>".$itemsCount."</m:count>";
        }

        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        echo Utils::ReplaceInFile(Path::Combine($this->_path,$this->apiVersion,"resources","entrytemplatepre.xml"),$r);

        if($setupLatest){
            for($i=sizeof($allRows)-1;$i>=0;$i--)
            {
                $allRows[$i]->IsAbsoluteLatestVersion=false;
                $allRows[$i]->IsLatestVersion=false;
            }
            if(sizeof($allRows)>0){
                $allRows[sizeof($allRows)-1]->IsAbsoluteLatestVersion =true;
            }
            for($i=sizeof($allRows)-1;$i>=0;$i--)
            {
                $row = $allRows[$i];
                if($row->IsPreRelease!=true){
                    $row->IsLatestVersion=true;
                    break;
                }
            }
        }

        for($i=0;$i<sizeof($allRows) && $i<$pg->Top;$i++)
        {
            $row = $allRows[$i];

            echo $this->_buildNuspecEntity($baseUrl,$row);
        }


        if(sizeof($allRows)>=$pg->Top){
            $act = strtolower($request->getParam("action",null));
            if($act=="packages") $act = "Packages";
            if($act=="search") $act = "Search";
            if($act=="findpackagesbyd") $act = "FindPackagesById";
            if($act=="getupdates") $act = "GetUpdates";
            $nq = $this->_setupLastQuery();
            if($act!="Search"){
                $nq = "\$skip=".($pg->Skip+$pg->Top)."&amp;\$top=".$pg->Top.$nq;
                echo "<link rel=\"next\" href=\"".$baseUrl."/api/".$this->_version."/".$act."?".$nq."\"/>";
            }
            /*?><link rel="next" href="http://localhost:8020/phpnuget/api/v2/Search?$skip=30&amp;$top=30&amp;searchTerm=''&amp;$filter=IsAbsoluteLatestVersion&amp;$orderby=DownloadCount+desc%2CId"/><?php*/
        }

        echo Utils::ReplaceInFile(Path::Combine($this->_path,$this->apiVersion,"resources","entrytemplatepost.xml"),$r);

        die();
    }
}