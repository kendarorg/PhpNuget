<?php

namespace lib\nuget;

use lib\db\FileDbStorageTestUtils;
use lib\db\QueryParser;
use lib\utils\PathUtils;
use lib\utils\Properties;
use PHPUnit\Framework\TestCase;

class NugetSpecialSearches extends TestCase
{
    private FileDbStorageTestUtils $utils;
    private $path;
    private $rootPath;
    private $queryParser;
    private $properties;

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
        $this->queryParser = new QueryParser();
        $this->properties = new Properties(null);
        $this->properties->setProperty("databaseRoot",$this->rootPath);
    }

    public function testShouldAddData()
    {
        $this->resetDb();
    }

}