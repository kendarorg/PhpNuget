<?php

namespace lib\rest;

use lib\http\Request;
use lib\rest\utils\NugetQuery;
use lib\rest\utils\NugetQueryHandler;
use lib\rest\utils\NugetResultParser;
use lib\rest\utils\Pagination;
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
        $query = "Id eq '".$id."' and Listed eq true orderby Id asc,Version asc";
        $nugetQuery = new NugetQuery();
        $nugetQuery->query = $query;
        $nugetQuery->pagination = (new Pagination())->buildFromRequest($request);
        $nugetQuery->setupLatest = true;
        $nugetQuery->count = $request->getBoolean("count",false);
        $nugetQuery->lineCount = strtolower($request->getParam("\$inlinecount", "none"))=="allpages";
        $nugetQuery->baseUrl = HttpUtils::currentUrl($this->properties->getProperty("siteRoot"),$this->properties);
        $nugetQuery->xmlAction = "FindPackagesById";
        $result = $this->nugetQueryHandler->query($query);
        $xml = $this->nugetResultParser->parse($result,$request);
        $this->answerString($xml,"application/xml");
        return true;
    }
}