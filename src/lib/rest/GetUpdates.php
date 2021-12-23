<?php

namespace lib\rest;

use lib\http\BaseHandler;
use lib\http\HandlerException;
use lib\http\Request;
use lib\rest\utils\NugetQuery;
use lib\rest\utils\NugetQueryHandler;
use lib\rest\utils\NugetResultParser;
use lib\rest\utils\Pagination;
use lib\rest\utils\ResourcesLoader;
use lib\utils\HttpUtils;
use lib\utils\Properties;

class GetUpdates extends BaseHandler
{
    /**
     * @var ResourcesLoader
     */
    private $resourcesLoader;
    /**
     * @var NugetQueryHandler
     */
    private $nugetQueryHandler;
    /**
     * @var NugetResultParser
     */
    private $nugetResultParser;

    /**
     * @param ResourcesLoader $resourcesLoader
     * @param Properties $properties
     * @param NugetQueryHandler $nugetQueryHandler
     * @param NugetResultParser $nugetResultParser
     */
    public function __construct($resourcesLoader, $properties, $nugetQueryHandler,$nugetResultParser)
    {
        parent::__construct($properties);
        $this->resourcesLoader = $resourcesLoader;
        $this->nugetQueryHandler = $nugetQueryHandler;
        $this->nugetResultParser = $nugetResultParser;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function catchAll($request)
    {
        $query = "";
        $packageIds = $request->getParam("packageIds");
        $versions = $request->getParam("versions");
        $includePrerelease = $request->getBoolean("includePrerelease",false);
        $includeAllVersions = $request->getParam("includeAllVersions");
        $targetFrameworks = $request->getParam("targetFrameworks");
        $versionConstraints = $request->getParam("versionConstraints");


        if($packageIds==null){
            throw new HandlerException("Missing package id", 500);
        }else{
            $packageIds =explode("|",$packageIds);
        }
        if($versions!=null){
            $versions =explode("|",$versions);
            if(sizeof($versions)!=sizeof($packageIds)){
                throw new HandlerException("Package ids must match versions", 500);
            }
            $tmp = array();
            for($i=0;$i<sizeof($versions);$i++){

                $tmp[] = "(Id eq '".trim($packageIds[$i],"'")."' and Version gt '".trim($versions[$i],"'")."')";
            }
            if(sizeof($tmp)>1){
                $query.="(".implode(" or ",$tmp).")";
            }else{
                $query.=$tmp[0];
            }
        }

        if(!$includePrerelease){
            $x = "(IsPreRelease eq false)";
            $query = $this->nugetQueryHandler->append($query,$x,"and");
        }

        $x = "(Listed eq true)";
        $query = $this->nugetQueryHandler->append($query,$x,"and");

        $query .=" orderby Title asc, Version desc";
        if($includeAllVersions!="true"){
            $query .=" groupby Id";
        }
        $nugetQuery = new NugetQuery();
        $nugetQuery->query = $query;
        $nugetQuery->pagination = (new Pagination())->buildFromRequest($request);
        $nugetQuery->setupLatest = true;
        $nugetQuery->count = $request->getBoolean("count",false);
        $nugetQuery->lineCount = strtolower($request->getParam("\$inlinecount", "none"))=="allpages";
        $nugetQuery->baseUrl = HttpUtils::currentUrl("",$this->properties);
        $nugetQuery->xmlAction = "FindSingle";
        $result = $this->nugetQueryHandler->query($query);
        $xml = $this->nugetResultParser->parse($result,$request);
        $this->answerString($xml,"application/xml");
        return true;
    }

}