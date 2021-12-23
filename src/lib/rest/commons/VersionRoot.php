<?php

namespace lib\rest\commons;

use lib\http\BaseHandler;
use lib\http\Request;
use lib\rest\utils\ResourcesLoader;
use lib\utils\Properties;

class VersionRoot extends BaseHandler
{
    /**
     * @var ResourcesLoader
     */
    private $resourcesLoader;

    /**
     * @param ResourcesLoader $resourcesLoader
     * @param Properties $properties
     */
    public function __construct($resourcesLoader, $properties)
    {
        parent::__construct($properties);
        $this->resourcesLoader = $resourcesLoader;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function catchAll($request){
        $replacements = array();
        $replacements["@Base@"] = trim(UrlUtils::CurrentUrl("",$this->properties),"/");
        $root = $this->resourcesLoader->getResource($replacements,"root.xml");
    }
}