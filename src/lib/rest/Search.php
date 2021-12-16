<?php

namespace lib\rest;

use lib\http\BaseHandler;
use lib\http\Request;
use lib\utils\PathUtils;

class Search extends BaseQueryable
{

    /**
     * @param Request $request
     * @return bool
     */
    public function catchAll($request){
        //$path = PathUtils::combine($this->root,$this->version,"resources","metadata.xml");
        //$this->answerFile($path,"application/xml");
        $query = "";
        $searchTerm = $request->getParam("searchTerm",null);
        $targetFramework = $request->getParam("targetFramework",null);
        $includePrerelease = strtolower($request->getParam("includePrerelease",null));
        $filter = $request->getParam("\$filter",null);

        $orderby = $request->getParam("\$orderby",null);

        $id = $request->getParam("id",null);

        //Maybe allow UrlUtils to check without case sensitivity?
        if($id==null){
            $id = $request->getParam("Id",null);
        }

        if($id!=null){
            $id = trim($id,"'");
            $x = "(Id eq '".$id."')";
            $query = $this->_append($query,$x,"and");
        }
        $version = $request->getParam("version",null);
        if($version==null){
            $version = $request->getParam("Version",null);
        }

        if($version!=null){
            $version = trim($version,"'");
            $x = "(Version eq '".$version."')";
            $query = $this->_append($query,$x,"and");
        }

        if($targetFramework!=null && $targetFramework!="" && $targetFramework!="''"){
            $targetFramework = urldecode(trim($targetFramework,"'"));
            $tf = explode("|",$targetFramework);
            $ar = array();
            $tt = array();
            foreach($tf as $ti){
                if(!in_array($ti,$ar)){
                    $ar[]=$ti;
                    $tt[]=" substringof('".$ti."',TargetFramework) ";
                }
            }
            $x = "(TargetFramework eq '' or (".implode("and",$tt)."))";
            $query = $this->_append($query,$x,"and");
        }

        if($includePrerelease==null){
            if($filter=="IsLatestVersion"){
                $filter = null;
                $query = $this->_append($query,"(IsPreRelease eq false)","and");
            }else if($filter=="IsAbsoluteLatestVersion"){
                $filter = null;
            }
        }else if(strtolower($includePrerelease)=="false"){
            $x = "(IsPreRelease eq false)";
            $query = $this->_append($query,$x,"and");
            if($filter=="IsLatestVersion" || $filter=="IsAbsoluteLatestVersion"){
                $filter = null;
            }
        }else if(strtolower($includePrerelease)=="true"){
            if($filter=="IsLatestVersion" || $filter=="IsAbsoluteLatestVersion"){
                $filter = null;
            }
        }
        if($filter=="IsLatestVersion" || $filter=="IsAbsoluteLatestVersion"){
            $filter = null;
        }




        if($searchTerm!=null && strlen($searchTerm)>0){
            if($searchTerm!="''"){
                $searchTerm = trim($searchTerm,"'");
                $x = "(";
                $x.= "substringof('".$searchTerm."',Title) or ";
                $x.= "substringof('".$searchTerm."',Id) or ";
                $x.= "substringof('".$searchTerm."',Description))";
                $query = $this->_append($query,$x,"and");
                $query = $this->_append($query," Listed eq true","and");
            }
        }


        $query = $this->_append($query,"(Listed eq true)","and");



        if($filter!=null){
            $x = "(".urldecode($filter).")";
            $query = $this->_append($query,$x,"and");
        }
        if($orderby!=null){
            $query =$query." orderby Id asc,Version desc, ".$orderby;
        }

        if($orderby==null){
            $query =$query." orderby Id asc,Version desc";
        }
        $query =$query." groupby Id";
        $this->_query($query);
        return true;
    }

}