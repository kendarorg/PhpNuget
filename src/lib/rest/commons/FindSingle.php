<?php



class FindSingle extends BaseHandler
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
        $version= $request->getParam("version");
        $query = "Id eq '".$id."' and Version eq '".$version."'";
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