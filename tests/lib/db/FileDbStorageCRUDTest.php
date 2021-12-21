<?php

namespace lib\db;

use lib\db\file\FileDbStorage;
use lib\nuget\models\NugetPackage;
use lib\nuget\NugetPackages;
use lib\nuget\NugetVersionType;
use lib\utils\PathUtils;
use lib\utils\Properties;
use PHPUnit\Framework\TestCase;

class FileDbStorageCRUDTest extends TestCase
{
    private FileDbStorageTestUtils $utils;
    private $path;
    private $rootPath;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->utils = new FileDbStorageTestUtils();
    }

    private function resetDb(){
        $this->path = dirname(dirname(__DIR__));
        $this->rootPath = PathUtils::combine($this->path,"data");
        $this->path = PathUtils::combine($this->path,"data","packages.json");
        if(file_exists($this->path)){
            unlink($this->path);
        }
        file_put_contents($this->path,"[]");
    }

    public function testShouldAddData(){
        $this->resetDb();
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $properties->setProperty("databaseRoot",$this->rootPath);

        $storage = new FileDbStorage($properties, $queryParser);
        $target = new NugetPackages($storage);

        $package = new NugetPackage();
        $package->Id="Pack";
        $package->Version = "1.0.0";

        $target->update($package);

        $this->assertTrue(file_exists($this->path));
        $this->assertTrue(filesize($this->path)>10);

        $storage = new FileDbStorage($properties, $queryParser);
        $target = new NugetPackages($storage);

        $result = $target->getByKey("Pack","1.0.0");
        $this->assertNotNull($result);
    }
}