<?php

namespace integration;

use lib\nuget\models\NugetPackage;
use lib\nuget\models\NugetUser;
use lib\OminousFactory;
use lib\rest\commons\ApiRoot;
use lib\utils\PathUtils;
use lib\utils\StringUtils;
use PHPUnit\Framework\TestCase;


class ApiRootTest extends TestCase
{
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->utils = new IntegrationUtils();
    }

    private $utils;
    

    public function testApiRootNotFound(){
        $this->utils->initializeBasic("get");
        //$rawContent = null,$files = array(), $extraData = array()
        $this->utils->addRequestParams(["id"=>"test","version"=>"1.0.0"]);


        $nugetPackages = OminousFactory::getObject("nugetPackages");
        $nugetUsers = OminousFactory::getObject("nugetUsers");
        $nugetDownloads = OminousFactory::getObject("nugetDownloads");

        $root = new ApiRoot($this->utils->properties,$nugetPackages,$nugetUsers,$nugetDownloads);
        $root->handle();
        $this->assertEquals("No results found",$this->utils->getRequestContent());
        $this->assertEquals(404,$this->utils->getRequestCode());
    }


    public function testApiRootFoundPackageNoFile(){
        $this->utils->initializeBasic("get");
        $this->utils->addRequestParams(["id"=>"test","version"=>"1.0.0"]);


        $nugetPackages = OminousFactory::getObject("nugetPackages");
        $nugetPackage = new NugetPackage();
        $nugetPackage->Id = "test";
        $nugetPackage->Version = "1.0.0";
        $nugetPackage->UserId = "132123";
        $nugetPackages->update($nugetPackage);
        $nugetUsers = OminousFactory::getObject("nugetUsers");
        $nugetDownloads = OminousFactory::getObject("nugetDownloads");

        $root = new ApiRoot($this->utils->properties,$nugetPackages,$nugetUsers,$nugetDownloads);
        $root->handle();
        $this->assertEquals("No file found",$this->utils->getRequestContent());
        $this->assertEquals(404,$this->utils->getRequestCode());
    }



    public function testApiRootFoundPackageFile(){
        $this->utils->initializeBasic("get");
        $this->utils->addRequestParams(["id"=>"test","version"=>"1.0.0"]);

        $this->utils->addNupkgFile("test.1.0.0.nupkg");


        $nugetPackages = OminousFactory::getObject("nugetPackages");
        $nugetPackage = new NugetPackage();
        $nugetPackage->Id = "test";
        $nugetPackage->Version = "1.0.0";
        $nugetPackage->UserId = "132123";
        $nugetPackages->update($nugetPackage);
        $nugetUsers = OminousFactory::getObject("nugetUsers");
        $nugetDownloads = OminousFactory::getObject("nugetDownloads");

        $root = new ApiRoot($this->utils->properties,$nugetPackages,$nugetUsers,$nugetDownloads);
        $root->handle();
        $this->assertEquals("",$this->utils->getRequestContent());
        $this->assertEquals(200,$this->utils->getRequestCode());
        $this->assertTrue($this->utils->requestFileContains("test.1.0.0.nupkg"));
    }

    public function testApiRootFoundPackageFileSymbol(){
        $this->utils->initializeBasic("get");
        $this->utils->addRequestParams(["id"=>"test","version"=>"1.0.0","symbol"=>"true"]);

        $this->utils->addNupkgFile("test.1.0.0.snupkg");


        $nugetPackages = OminousFactory::getObject("nugetPackages");
        $nugetPackage = new NugetPackage();
        $nugetPackage->Id = "test";
        $nugetPackage->Version = "1.0.0";
        $nugetPackage->UserId = "132123";
        $nugetPackages->update($nugetPackage);
        $nugetUsers = OminousFactory::getObject("nugetUsers");
        $nugetDownloads = OminousFactory::getObject("nugetDownloads");

        $root = new ApiRoot($this->utils->properties,$nugetPackages,$nugetUsers,$nugetDownloads);
        $root->handle();
        $this->assertEquals("",$this->utils->getRequestContent());
        $this->assertEquals(200,$this->utils->getRequestCode());
        $this->assertTrue($this->utils->requestFileContains("test.1.0.0.snupkg"));
    }

    public function testApiRootFoundPackageDeleteNoUser(){
        $this->utils->initializeBasic("delete");
        //$rawContent = null,$files = array(), $extraData = array()
        $this->utils->addRequestParams(["id"=>"test","version"=>"1.0.0","apiKey"=>"apiKey"]);

        $this->utils->addNupkgFile("test.1.0.0.snupkg");


        $nugetPackages = OminousFactory::getObject("nugetPackages");
        $nugetPackage = new NugetPackage();
        $nugetPackage->Id = "test";
        $nugetPackage->Version = "1.0.0";
        $nugetPackage->UserId = "132123";
        $nugetPackages->update($nugetPackage);
        $nugetUsers = OminousFactory::getObject("nugetUsers");
        $nugetDownloads = OminousFactory::getObject("nugetDownloads");

        $root = new ApiRoot($this->utils->properties,$nugetPackages,$nugetUsers,$nugetDownloads);
        $root->handle();
        $this->assertEquals("No results found",$this->utils->getRequestContent());
        $this->assertEquals(404,$this->utils->getRequestCode());
    }


    public function testApiRootFoundPackageDelete(){
        $this->utils->initializeBasic("delete");
        //$rawContent = null,$files = array(), $extraData = array()
        $this->utils->addRequestParams(["id"=>"test","version"=>"1.0.0","setPrerelease"=>null,"apiKey"=>"apiKey"]);

        $nup = PathUtils::combine($this->utils->properties->getProperty("packagesRoot"),"test.1.0.0.snupkg");
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

        $root = new ApiRoot($this->utils->properties,$nugetPackages,$nugetUsers,$nugetDownloads);
        $root->handle();
        $this->assertEquals("",$this->utils->getRequestContent());
        $this->assertEquals(200,$this->utils->getRequestCode());

        $pack  = $nugetPackages->getByKey("test","1.0.0");
        $this->assertTrue($pack->IsPreRelease);
    }

    public function testApiRootFoundPackageList(){
        $this->utils->initializeBasic("post");
        //$rawContent = null,$files = array(), $extraData = array()
        $this->utils->addRequestParams(["id"=>"test","version"=>"1.0.0","apiKey"=>"apiKey"]);

        $nup = PathUtils::combine($this->utils->properties->getProperty("packagesRoot"),"test.1.0.0.snupkg");
        file_put_contents($nup,"test");


        $nugetPackages = OminousFactory::getObject("nugetPackages");
        $nugetPackage = new NugetPackage();
        $nugetPackage->Id = "test";
        $nugetPackage->Version = "1.0.0";
        $nugetPackage->UserId = "132123";
        $nugetPackage->Listed = false;
        $nugetPackages->update($nugetPackage);

        $nugetUsers = OminousFactory::getObject("nugetUsers");
        $nugetUser = new NugetUser();
        $nugetUser->Id="132123";
        $nugetUser->UserId="userId";
        $nugetUser->Token = "{apiKey}";
        $nugetUsers->update($nugetUser);

        $nugetDownloads = OminousFactory::getObject("nugetDownloads");

        $root = new ApiRoot($this->utils->properties,$nugetPackages,$nugetUsers,$nugetDownloads);
        $root->handle();
        $this->assertEquals("",$this->utils->getRequestContent());
        $this->assertEquals(200,$this->utils->getRequestCode());

        $pack  = $nugetPackages->getByKey("test","1.0.0");
        $this->assertTrue($pack->Listed);
    }
}