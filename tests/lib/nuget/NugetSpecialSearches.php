<?php

namespace lib\nuget;

use lib\db\file\FileDbStorage;
use lib\db\file\FileDbStorageTestUtils;
use lib\db\QueryParser;
use lib\nuget\fields\file\ArraysCompositeField;
use lib\nuget\fields\file\NugetVersionType;
use lib\nuget\models\NugetPackage;
use lib\utils\Properties;
use PHPUnit\Framework\TestCase;

class NugetSpecialSearches extends TestCase
{
    private FileDbStorageTestUtils $utils;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->utils = new FileDbStorageTestUtils();
    }

    public function testBasic()
    {
        $items = array();
        $items[] = $item1 =$this->utils->buildNewItem("Pack1", "1.0.0.0");
        $items[] = $item2 = $this->utils->buildNewItem("Pack2", "1.0.0.0");
        $item1->Author = ["a","b"];
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $query = "Author eq 'a'";

        $target = new FileDbStorage($properties, $queryParser, $items);
        $target->initialize(array(), [new NugetVersionType(),new ArraysCompositeField()],
            new NugetPackage(),new DependencyCompositeField());

        $result = $target->query($query, -1, 0);

        $this->assertEquals(1, sizeof($result));
        $this->assertEquals("Pack1", $result[0]->Id);
    }

}