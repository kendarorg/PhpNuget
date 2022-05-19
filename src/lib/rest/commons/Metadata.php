<?php

namespace lib\rest\commons;

use lib\http\BaseHandler;
use lib\http\Request;
use lib\utils\PathUtils;

/**
 * https://stackoverflow.com/questions/51789617/php-get-url-of-current-file-directory
 */
class Metadata extends BaseHandler
{
    /**
     * @var string
     */
    private $root;

    /**
     * @var string
     */
    private $version;

    /**
     * @param string $version
     */
    public function __construct($version)
    {
        $this->root = dirname(__FILE__);
        $this->version = $version;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function catchAll($request){
        $path = PathUtils::combine($this->root,$this->version,"resources","metadata.xml");
        $this->answerFile($path,"application/xml");
        return true;
    }
}