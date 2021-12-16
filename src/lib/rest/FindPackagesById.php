<?php

namespace lib\rest;

use lib\http\Request;
use lib\rest\utils\NugetQuery;
use lib\rest\utils\NugetQueryHandler;
use lib\rest\utils\NugetResultParser;
use lib\rest\utils\ResourcesLoader;
use lib\utils\HttpUtils;
use lib\utils\Properties;

class FindPackagesById
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
        $id = $request->getParam("id");
        if($id!=null){
            $id = trim($id,"'");
        }
        $query = "Id eq '".$id."' and Listed eq true orderby Id asc,Version asc";
        $nugetQuery = new NugetQuery();
        $nugetQuery->query = $query;
        $nugetQuery->setupLatest = true;
        $nugetQuery->count = $request->getBoolean("count",false);
        $nugetQuery->lineCount = strtolower($request->getParam("\$inlinecount", "none"))=="allpages";
        $nugetQuery->baseUrl = HttpUtils::currentUrl($this->properties->getProperty("siteRoot"),$this->properties);
        $nugetQuery->xmlAction = "FindPackagesById";
        $result = $this->nugetQueryHandler->query($query);
        $lastQuery = $this->buildLastQuery($request);
        $xml = $this->nugetResultParser->parse($result,$lastQuery);
        $this->answerString($xml,"application/xml");
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