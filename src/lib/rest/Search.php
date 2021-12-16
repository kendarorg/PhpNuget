<?php

namespace lib\rest;

use lib\http\BaseHandler;
use lib\http\Request;
use lib\utils\HttpUtils;
use lib\utils\PathUtils;
use lib\utils\Properties;

class Search extends BaseHandler
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
     * @param $top
     * @param $verbs
     * @return Pagination
     */
    private function getPagination($request,$top=10,$verbs = "all")
    {
        $pg = new Pagination();
        $pg->Skip = $request->getInteger("\$skip",0);
        $pg->Top = $request->getInteger("\$top",1000);
        return $pg;
    }
    /**
     * @param Request $request
     * @return bool
     */
    public function catchAll($request)
    {
        $nugetQuery = new NugetQuery();
        $nugetQuery->pagination = $this->getPagination($request);
        $nugetQuery->xmlAction = "Search";
        $nugetQuery->count = $request->getBoolean("count",false);
        $nugetQuery->lineCount = strtolower($request->getParam("\$inlinecount", "none"))=="allpages";
        $nugetQuery->baseUrl = HttpUtils::currentUrl($this->properties->getProperty("siteRoot"),$this->properties);

        $nugetQuery->searchTerm = $request->getParam("searchTerm", null);
        $nugetQuery->targetFramework = $request->getParam("targetFramework", null);
        $nugetQuery->includePrerelease = $request->getBoolean("includePrerelease", null);
        $nugetQuery->includePrereleaseSet = $request->getParam("includePrerelease", null)!=null;
        $nugetQuery->filter = $request->getParam("\$filter", null);

        $nugetQuery->orderby = $request->getParam("\$orderby", null);

        $nugetQuery->id = $request->getParam("id", null);
        $nugetQuery->version = $request->getParam("version", null);



        $result = $this->nugetQueryHandler->query($nugetQuery);
        $lastQuery = $this->buildLastQuery($request);
        $xml = $this->nugetResultParser->parse($result,$lastQuery);
        return true;
    }

    function buildLastQuery($request)
    {
        $lastQuery = array();

        $val = $request->getParam("packageIds",null);
        if($val!=null)$lastQuery["packageIds"]=$val;
        $val = $request->getParam("versions",null);
        if($val!=null)$lastQuery["versions"]=$val;
        $val = $request->getParam("includePrerelease","false");
        if($val!=null)$lastQuery["includePrerelease"]=$val;
        $val = $request->getParam("includeAllVersions",null);
        if($val!=null)$lastQuery["includeAllVersions"]=$val;
        $val = $request->getParam("targetFrameworks",null);
        if($val!=null)$lastQuery["targetFrameworks"]=$val;
        $val = $request->getParam("versionConstraints",null);
        if($val!=null)$lastQuery["versionConstraints"]=$val;
        $val = $request->getParam("searchTerm",null);
        if($val!=null)$lastQuery["searchTerm"]=$val;
        $val = $request->getParam("\$filter",null);
        if($val!=null)$lastQuery["\$filter"]=$val;
        $val = $request->getParam("\$orderby",null);
        if($val!=null)$lastQuery["\$orderby"]=$val;
        $val = $request->getParam("id",null);
        if($val!=null)$lastQuery["id"]=$val;
        return $lastQuery;
    }
}