<?php

namespace integration;

use lib\http\Request;
use lib\nuget\models\NugetPackage;
use lib\OminousFactory;
use lib\rest\commons\ApiRoot;
use lib\utils\PathUtils;
use PHPUnit\Framework\TestCase;


class ApiTests extends TestCase
{
    private function initializeBasic($verb){

        $_SERVER['REQUEST_METHOD'] = $verb;
        $path = dirname(__DIR__);
        $rootPath = PathUtils::combine($path,"data");

        foreach (glob(PathUtils::combine($rootPath,"*.json")) as $dir) {
            unlink($dir);
        }
        $this->properties = OminousFactory::getObject("properties");
        $this->properties->setProperty("databaseRoot",$rootPath);
        $this->request = new MockRequest();
        OminousFactory::setObject("request",$this->request);
    }

    public function testApiRootNotFound(){
        $this->initializeBasic("get");
        //$rawContent = null,$files = array(), $extraData = array()
        $this->request->addExtraData("id","test");
        $this->request->addExtraData("version","1.0.0");


        $nugetPackages = OminousFactory::getObject("nugetPackages");
        $nugetUsers = OminousFactory::getObject("nugetUsers");
        $nugetDownloads = OminousFactory::getObject("nugetDownloads");

        $root = new ApiRoot($this->properties,$nugetPackages,$nugetUsers,$nugetDownloads);
        $root->handle();
        $this->assertEquals("No results found",trim($this->request->content));
        $this->assertEquals(404,trim($this->request->responseCode));
    }


    public function testApiRootFoundPackageNoFile(){
        $this->initializeBasic("get");
        //$rawContent = null,$files = array(), $extraData = array()
        $this->request->addExtraData("id","test");
        $this->request->addExtraData("version","1.0.0");


        $nugetPackages = OminousFactory::getObject("nugetPackages");
        $nugetPackage = new NugetPackage();
        $nugetPackage->Id = "test";
        $nugetPackage->Version = "1.0.0";
        $nugetPackages->update($nugetPackage);
        $nugetUsers = OminousFactory::getObject("nugetUsers");
        $nugetDownloads = OminousFactory::getObject("nugetDownloads");

        $root = new ApiRoot($this->properties,$nugetPackages,$nugetUsers,$nugetDownloads);
        $root->handle();
        $this->assertEquals("No file found",trim($this->request->content));
        $this->assertEquals(404,trim($this->request->responseCode));
    }
}