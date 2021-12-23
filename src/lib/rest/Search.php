<?php

namespace lib\rest;

use lib\http\BaseHandler;
use lib\http\Request;
use lib\rest\utils\NugetQuery;
use lib\rest\utils\NugetQueryHandler;
use lib\rest\utils\NugetResultParser;
use lib\rest\utils\Pagination;
use lib\rest\utils\ResourcesLoader;
use lib\utils\HttpUtils;
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
     * @return bool
     */
    public function catchAll($request)
    {
        $nugetQuery = new NugetQuery();
        $nugetQuery->pagination = (new Pagination())->buildFromRequest($request);
        $nugetQuery->xmlAction = "Search";
        $nugetQuery->count = $request->getBoolean("count",false);
        $nugetQuery->lineCount = strtolower($request->getParam("\$inlinecount", "none"))=="allpages";
        $nugetQuery->baseUrl = HttpUtils::currentUrl("",$this->properties);

        $nugetQuery->searchTerm = $request->getParam("searchTerm", null);
        $nugetQuery->targetFramework = $request->getParam("targetFramework", null);
        $nugetQuery->includePrerelease = $request->getBoolean("includePrerelease", null);
        $nugetQuery->includePrereleaseSet = $request->getParam("includePrerelease", null)!=null;
        $nugetQuery->filter = $request->getParam("\$filter", null);

        $nugetQuery->orderby = $request->getParam("\$orderby", null);

        $nugetQuery->id = $request->getParam("id", null);
        $nugetQuery->version = $request->getParam("version", null);



        $result = $this->nugetQueryHandler->search($nugetQuery);
        $xml = $this->nugetResultParser->parse($result,$request);
        $this->answerString($xml,"application/xml");
        return true;
    }
}