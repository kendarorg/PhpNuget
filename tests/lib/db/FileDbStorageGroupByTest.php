<?php

namespace lib\db;

use lib\db\file\FileDbStorage;
use lib\db\FileDbStorageBase;
use lib\nuget\fields\file\NugetVersionType;
use lib\nuget\models\NugetPackage;
use lib\utils\Properties;
use PHPUnit\Framework\TestCase;

class FileDbStorageGroupByTest extends TestCase
{
    private FileDbStorageTestUtils $utils;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->utils = new FileDbStorageTestUtils();
    }

    public function testGroupByBasic()
    {
        $items = array();
        $items[] = $this->utils->buildNewItem("Pack2", "1.0.0.0");
        $items[] = $this->utils->buildNewItem("Pack1", "1.0.0.0");
        $items[] = $this->utils->buildNewItem("Pack1", "1.0.0.1");
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $query = "Id groupby Id";

        $target = new FileDbStorage($properties, $queryParser, $items);
        $target->initialize(array(), [new NugetVersionType()], new NugetPackage());

        $result = $target->query($query, -1, 0);

        $this->assertEquals(2, sizeof($result));
        $this->assertEquals("Pack2", $result[0]->Id);
        $this->assertEquals("Pack1", $result[1]->Id);
    }

    public function testGroupByWithOderBy()
    {
        $items = array();
        $items[] = $this->utils->buildNewItem("Pack2", "1.0.0.0");
        $items[] = $this->utils->buildNewItem("Pack1", "1.0.0.0");
        $items[] = $this->utils->buildNewItem("Pack1", "1.0.0.1");
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $query = "Id groupby Id ASC orderby id asc";

        $target = new FileDbStorage($properties, $queryParser, $items);
        $target->initialize(array(), [new NugetVersionType()], new NugetPackage());

        $result = $target->query($query, -1, 0);

        $this->assertEquals(2, sizeof($result));
        $this->assertEquals("Pack1", $result[0]->Id);
        $this->assertEquals("Pack2", $result[1]->Id);
    }

    public function testGroupByWithOderByAndFakeAggregations()
    {
        $items = array();
        $items[] = $this->utils->buildNewItem("Pack2", "2.0.0.0");
        $items[] = $this->utils->buildNewItem("Pack1", "1.0.0.0");
        $items[] = $this->utils->buildNewItem("Pack1", "1.0.0.1");
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $query = "Id,version groupby Id ASC orderby Id asc, version desc";

        $target = new FileDbStorage($properties, $queryParser, $items);
        $target->initialize(array(), [new NugetVersionType()], new NugetPackage());

        $result = $target->query($query, -1, 0);

        $this->assertEquals(2, sizeof($result));
        $this->assertEquals("Pack1", $result[0]->Id);
        $this->assertEquals("1.0.0.1", $result[0]->Version);
        $this->assertEquals(2, $result[0]->count);
        $this->assertEquals("Pack2", $result[1]->Id);
        $this->assertEquals("2.0.0.0", $result[1]->Version);
        $this->assertEquals(1, $result[1]->count);
    }
}