<?php

namespace lib\db;

use lib\db\file\FileDbStorage;
use lib\db\FileDbStorageBase;
use lib\nuget\fields\file\NugetVersionType;
use lib\nuget\models\NugetPackage;
use lib\utils\Properties;
use PHPUnit\Framework\TestCase;

class FileDbStorageOrderTest extends TestCase
{
    private FileDbStorageTestUtils $utils;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->utils = new FileDbStorageTestUtils();
    }

    public function testOrderBasicAsc()
    {
        $items = array();
        $items[] = $this->utils->buildNewItem("Pack2", "1.0.0.0");
        $items[] = $this->utils->buildNewItem("Pack1", "1.0.0.0");
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $query = "Orderby Id ASC";

        $target = new FileDbStorage($properties, $queryParser, $items);
        $target->initialize(array(), [new NugetVersionType()], new NugetPackage());

        $result = $target->query($query, -1, 0);

        $this->assertEquals(2, sizeof($result));
        $this->assertEquals("Pack1", $result[0]->Id);
    }

    public function testOrderBasicDesc()
    {
        $items = array();
        $items[] = $this->utils->buildNewItem("Pack1", "1.0.0.0");
        $items[] = $this->utils->buildNewItem("Pack2", "1.0.0.0");
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $query = "Orderby Id desc";

        $target = new FileDbStorage($properties, $queryParser, $items);
        $target->initialize(array(), [new NugetVersionType()], new NugetPackage());

        $result = $target->query($query, -1, 0);

        $this->assertEquals(2, sizeof($result));
        $this->assertEquals("Pack2", $result[0]->Id);
    }

    public function testOrderBasicDescOrdered()
    {
        $items = array();
        $items[] = $this->utils->buildNewItem("Pack2", "1.0.0.0");
        $items[] = $this->utils->buildNewItem("Pack1", "1.0.0.0");
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $query = "Orderby Id desc";

        $target = new FileDbStorage($properties, $queryParser, $items);
        $target->initialize(array(), [new NugetVersionType()], new NugetPackage());

        $result = $target->query($query, -1, 0);

        $this->assertEquals(2, sizeof($result));
        $this->assertEquals("Pack2", $result[0]->Id);
    }

    public function testVersionAscOrdered()
    {
        $items = array();
        $items[] = $this->utils->buildNewItem("Pack1", "1.0.0.0");
        $items[] = $this->utils->buildNewItem("Pack2", "1.0.0.1");
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $query = "orderby version asc";

        $target = new FileDbStorage($properties, $queryParser, $items);
        $target->initialize(array(), [new NugetVersionType()], new NugetPackage());

        $result = $target->query($query, -1, 0);

        $this->assertEquals(2, sizeof($result));
        $this->assertEquals("Pack1", $result[0]->Id);
        $this->assertEquals("Pack2", $result[1]->Id);
    }

    public function testVersionAsc()
    {
        $items = array();
        $items[] = $this->utils->buildNewItem("Pack1", "1.0.0.1");
        $items[] = $this->utils->buildNewItem("Pack2", "1.0.0.0");
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $query = "orderby version asc";

        $target = new FileDbStorage($properties, $queryParser, $items);
        $target->initialize(array(), [new NugetVersionType()], new NugetPackage());

        $result = $target->query($query, -1, 0);

        $this->assertEquals(2, sizeof($result));
        $this->assertEquals("Pack2", $result[0]->Id);
        $this->assertEquals("Pack1", $result[1]->Id);
    }

    public function testVersionDesc()
    {
        $items = array();
        $items[] = $this->utils->buildNewItem("Pack2", "1.0.0.0");
        $items[] = $this->utils->buildNewItem("Pack1", "1.0.0.1");
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $query = "orderby version desc";

        $target = new FileDbStorage($properties, $queryParser, $items);
        $target->initialize(array(), [new NugetVersionType()], new NugetPackage());

        $result = $target->query($query, -1, 0);

        $this->assertEquals(2, sizeof($result));
        $this->assertEquals("Pack1", $result[0]->Id);
        $this->assertEquals("Pack2", $result[1]->Id);
    }

    public function testVersionDescOrdered()
    {
        $items = array();
        $items[] = $this->utils->buildNewItem("Pack1", "1.0.0.1");
        $items[] = $this->utils->buildNewItem("Pack2", "1.0.0.0");
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $query = "orderby version desc";

        $target = new FileDbStorage($properties, $queryParser, $items);
        $target->initialize(array(), [new NugetVersionType()], new NugetPackage());

        $result = $target->query($query, -1, 0);

        $this->assertEquals(2, sizeof($result));
        $this->assertEquals("Pack1", $result[0]->Id);
        $this->assertEquals("Pack2", $result[1]->Id);
    }

}