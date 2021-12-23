<?php

namespace integration;

use lib\nuget\models\NugetPackage;
use lib\nuget\models\NugetUser;
use lib\OminousFactory;
use lib\rest\commons\ApiRoot;
use lib\utils\PathUtils;
use lib\utils\StringUtils;
use PHPUnit\Framework\TestCase;


class ApiRootDirectFindPackage extends TestCase
{
    private function initializeBasic($verb){

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
        $nugetPackage->UserId = "132123";
        $nugetPackages->update($nugetPackage);
        $nugetUsers = OminousFactory::getObject("nugetUsers");
        $nugetDownloads = OminousFactory::getObject("nugetDownloads");

        $root = new ApiRoot($this->properties,$nugetPackages,$nugetUsers,$nugetDownloads);
        $root->handle();
        $this->assertEquals("No file found",trim($this->request->content));
        $this->assertEquals(404,trim($this->request->responseCode));
    }



    public function testApiRootFoundPackageFile(){
        $this->initializeBasic("get");
        //$rawContent = null,$files = array(), $extraData = array()
        $this->request->addExtraData("id","test");
        $this->request->addExtraData("version","1.0.0");

        $nup = PathUtils::combine($this->properties->getProperty("packagesRoot"),"test.1.0.0.nupkg");
        file_put_contents($nup,"test");


        $nugetPackages = OminousFactory::getObject("nugetPackages");
        $nugetPackage = new NugetPackage();
        $nugetPackage->Id = "test";
        $nugetPackage->Version = "1.0.0";
        $nugetPackage->UserId = "132123";
        $nugetPackages->update($nugetPackage);
        $nugetUsers = OminousFactory::getObject("nugetUsers");
        $nugetDownloads = OminousFactory::getObject("nugetDownloads");

        $root = new ApiRoot($this->properties,$nugetPackages,$nugetUsers,$nugetDownloads);
        $root->handle();
        $this->assertEquals("",trim($this->request->content));
        $this->assertEquals(200,trim($this->request->responseCode));
        $this->assertTrue(StringUtils::endsWith($this->request->readFile,"test.1.0.0.nupkg"));
    }

    public function testApiRootFoundPackageFileSymbol(){
        $this->initializeBasic("get");
        //$rawContent = null,$files = array(), $extraData = array()
        $this->request->addExtraData("id","test");
        $this->request->addExtraData("version","1.0.0");
        $this->request->addExtraData("symbol","true");

        $nup = PathUtils::combine($this->properties->getProperty("packagesRoot"),"test.1.0.0.snupkg");
        file_put_contents($nup,"test");


        $nugetPackages = OminousFactory::getObject("nugetPackages");
        $nugetPackage = new NugetPackage();
        $nugetPackage->Id = "test";
        $nugetPackage->Version = "1.0.0";
        $nugetPackage->UserId = "132123";
        $nugetPackages->update($nugetPackage);
        $nugetUsers = OminousFactory::getObject("nugetUsers");
        $nugetDownloads = OminousFactory::getObject("nugetDownloads");

        $root = new ApiRoot($this->properties,$nugetPackages,$nugetUsers,$nugetDownloads);
        $root->handle();
        $this->assertEquals("",trim($this->request->content));
        $this->assertEquals(200,trim($this->request->responseCode));
        $this->assertTrue(StringUtils::endsWith($this->request->readFile,"test.1.0.0.snupkg"));
    }

    public function testApiRootFoundPackageDeleteNoUser(){
        $this->initializeBasic("delete");
        //$rawContent = null,$files = array(), $extraData = array()
        $this->request->addExtraData("id","test");
        $this->request->addExtraData("version","1.0.0");
        $this->request->addExtraData("apiKey","apiKey");

        $nup = PathUtils::combine($this->properties->getProperty("packagesRoot"),"test.1.0.0.snupkg");
        file_put_contents($nup,"test");


        $nugetPackages = OminousFactory::getObject("nugetPackages");
        $nugetPackage = new NugetPackage();
        $nugetPackage->Id = "test";
        $nugetPackage->Version = "1.0.0";
        $nugetPackage->UserId = "132123";
        $nugetPackages->update($nugetPackage);
        $nugetUsers = OminousFactory::getObject("nugetUsers");
        $nugetDownloads = OminousFactory::getObject("nugetDownloads");

        $root = new ApiRoot($this->properties,$nugetPackages,$nugetUsers,$nugetDownloads);
        $root->handle();
        $this->assertEquals("No results found",trim($this->request->content));
        $this->assertEquals(404,trim($this->request->responseCode));
    }


    public function testApiRootFoundPackageDelete(){
        $this->initializeBasic("delete");
        //$rawContent = null,$files = array(), $extraData = array()
        $this->request->addExtraData("id","test");
        $this->request->addExtraData("version","1.0.0");
        $this->request->addExtraData("apiKey","apiKey");
        $this->request->addExtraData("setPrerelease",null);

        $nup = PathUtils::combine($this->properties->getProperty("packagesRoot"),"test.1.0.0.snupkg");
        file_put_contents($nup,"test");


        $nugetPackages = OminousFactory::getObject("nugetPackages");
        $nugetPackage = new NugetPackage();
        $nugetPackage->Id = "test";
        $nugetPackage->Version = "1.0.0";
        $nugetPackage->UserId = "132123";
        $nugetPackages->update($nugetPackage);

        $nugetUsers = OminousFactory::getObject("nugetUsers");
        $nugetUser = new NugetUser();
        $nugetUser->Id="132123";
        $nugetUser->UserId="userId";
        $nugetUser->Token = "{apiKey}";
        $nugetUsers->update($nugetUser);

        $nugetDownloads = OminousFactory::getObject("nugetDownloads");

        $root = new ApiRoot($this->properties,$nugetPackages,$nugetUsers,$nugetDownloads);
        $root->handle();
        $this->assertEquals("",trim($this->request->content));
        $this->assertEquals(200,trim($this->request->responseCode));

        $pack  = $nugetPackages->getByKey("test","1.0.0");
        $this->assertTrue($pack->IsPreRelease);
    }

    public function testApiRootFoundPackageList(){
        $this->initializeBasic("post");
        //$rawContent = null,$files = array(), $extraData = array()
        $this->request->addExtraData("id","test");
        $this->request->addExtraData("version","1.0.0");
        $this->request->addExtraData("apiKey","apiKey");

        $nup = PathUtils::combine($this->properties->getProperty("packagesRoot"),"test.1.0.0.snupkg");
        file_put_contents($nup,"test");


        $nugetPackages = OminousFactory::getObject("nugetPackages");
        $nugetPackage = new NugetPackage();
        $nugetPackage->Id = "test";
        $nugetPackage->Version = "1.0.0";
        $nugetPackage->UserId = "132123";
        $nugetPackages->update($nugetPackage);

        $nugetUsers = OminousFactory::getObject("nugetUsers");
        $nugetUser = new NugetUser();
        $nugetUser->Id="132123";
        $nugetUser->UserId="userId";
        $nugetUser->Token = "{apiKey}";
        $nugetUsers->update($nugetUser);

        $nugetDownloads = OminousFactory::getObject("nugetDownloads");

        $root = new ApiRoot($this->properties,$nugetPackages,$nugetUsers,$nugetDownloads);
        $root->handle();
        $this->assertEquals("",trim($this->request->content));
        $this->assertEquals(200,trim($this->request->responseCode));

        $pack  = $nugetPackages->getByKey("test","1.0.0");
        $this->assertTrue($pack->Listed);
    }
}