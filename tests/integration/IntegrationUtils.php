<?php

namespace integration;

use lib\OminousFactory;
use lib\utils\PathUtils;
use lib\utils\Properties;
use lib\utils\StringUtils;

class IntegrationUtils
{
    /**
     * @var Properties
     */
    public  $properties;
    /**
     * @var MockRequest
     */
    public  $request;

    public function initializeBasic($verb){

        $_SERVER['REQUEST_METHOD'] = $verb;
        $path = dirname(__DIR__);
        $rootPath = PathUtils::combine($path,"data");
        foreach (glob(PathUtils::combine($rootPath,"*.json")) as $dir) {
            unlink($dir);
        }
        $nupRoot = PathUtils::combine($path,"packages");
        foreach (glob(PathUtils::combine($nupRoot,"*.*nupkg")) as $dir) {
            unlink($dir);
        }
        $this->properties = OminousFactory::getObject("properties");
        $this->properties->setProperty("databaseRoot",$rootPath);
        $this->properties->setProperty("packagesRoot",$nupRoot);
        $this->request = new MockRequest();
        OminousFactory::setObject("request",$this->request);
    }

    public function addRequestParams($data){
        foreach ($data as $key=>$value){
            $this->request->addExtraData($key,$value);
        }
    }

    public function getRequestContent(){
        return trim($this->request->content);
    }
    public function contentContains($data){
        return StringUtils::indexOf(strtolower($this->request->content),strtolower($data))>=0;
    }
    public function getRequestCode(){
        return trim($this->request->responseCode);
    }
    public function getRequestFile(){
        return trim($this->request->readFile);
    }
    public function requestFileContains($data){
        return StringUtils::indexOf(strtolower($this->request->readFile),strtolower($data))>=0;
    }

    public function addNupkgFile($name,$content = "test"){
        $nup = PathUtils::combine($this->properties->getProperty("packagesRoot"),$name);
        file_put_contents($nup,$content);
    }
}