<?php

namespace lib\rest\utils;

use lib\utils\PathUtils;

class ResourcesLoader
{
    /**
     * @var string
     */
    public $version;

    public function __construct($version)
    {
        $this->version = $version;
        $this->path = PathUtils::combine(dirname(__DIR__),$version);
    }

    public function getResource($replacements,$source,$destination = null){
        $template = PathUtils::combine($this->path,$source);
        $content = file_get_contents($template);

        foreach ($replacements as $key => $value){
            $content = str_replace($key,$value,$content);
        }

        if($destination!=null){
            file_put_contents($destination,$content);
        }

        return $content;
    }
}