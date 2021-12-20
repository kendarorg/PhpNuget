<?php

namespace lib\db;

use lib\db\file\FileDbStorage;
use lib\db\FileDbStorageBase;
use lib\db\FileDbStorageTestUtils;
use lib\nuget\models\NugetPackage;
use lib\nuget\NugetVersionType;
use lib\utils\Properties;
use PHPUnit\Framework\TestCase;

class FileDbStorageSelectTest extends TestCase
{
    private FileDbStorageTestUtils $utils;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->utils = new FileDbStorageTestUtils();
    }

    public function testBasic(){
        $items = array();
        $items[] = $this->utils->buildNewItem("Pack1","1.0.0.0");
        $items[] = $this->utils->buildNewItem("Pack2","1.0.0.0");
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $query = "Id eq 'Pack1'";

        $target = new FileDbStorage($properties,$queryParser,$items);
        $result = $target->query($query,array(),-1,0,[new NugetVersionType()],new NugetPackage());

        $this->assertEquals(1,sizeof($result));
        $this->assertEquals("Pack1",$result[0]->Id);
    }

    public function testWithParenthesis(){
        $items = array();
        $items[] = $this->utils->buildNewItem("Pack1","1.0.0.0");
        $items[] = $this->utils->buildNewItem("Pack2","1.0.0.0");
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $query = "(Id eq 'Pack1') and true";

        $target = new FileDbStorage($properties,$queryParser,$items);
        $result = $target->query($query,array(),-1,0,[new NugetVersionType()],new NugetPackage());

        $this->assertEquals(1,sizeof($result));
        $this->assertEquals("Pack1",$result[0]->Id);
    }

    public function testVersion(){
        $items = array();
        $items[] = $this->utils->buildNewItem("Pack1","1.0.0.0");
        $items[] = $this->utils->buildNewItem("Pack2","1.0.0.0");
        $items[] = $this->utils->buildNewItem("Pack3","1.0.0.1");
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $query = "Version eq '1.0.0.0'";

        $target = new FileDbStorage($properties,$queryParser,$items);
        $result = $target->query($query,array(),-1,0,[new NugetVersionType()],new NugetPackage());

        $this->assertEquals(2,sizeof($result));
        $this->assertEquals("Pack1",$result[0]->Id);
        $this->assertEquals("Pack2",$result[1]->Id);
    }

    public function testVersionCompare(){
        $items = array();
        $items[] = $this->utils->buildNewItem("Pack1","1.0.0.0");
        $items[] = $this->utils->buildNewItem("Pack2","1.0.0.1");
        $items[] = $this->utils->buildNewItem("Pack3","1.0.0.2");
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $query = "Version gte '1.0.0.1'";

        $target = new FileDbStorage($properties,$queryParser,$items);
        $result = $target->query($query,array(),-1,0,[new NugetVersionType()],new NugetPackage());

        $this->assertEquals(2,sizeof($result));
        $this->assertEquals("Pack2",$result[0]->Id);
        $this->assertEquals("Pack3",$result[1]->Id);
    }
}