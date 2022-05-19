<?php

namespace lib\nuget;

use lib\db\file\FileDbStorage;
use lib\db\file\FileDbStorageTestUtils;
use lib\db\QueryParser;
use lib\nuget\fields\file\FileNugetVersionType;
use lib\nuget\models\NugetPackage;
use lib\utils\Properties;
use PHPUnit\Framework\TestCase;

class ChocolateyIdTest extends TestCase
{
    private FileDbStorageTestUtils $utils;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->utils = new FileDbStorageTestUtils();
    }

    /**
     * Lowercase id
     * @return void
     */
    public function testId()
    {
        $items = array();
        $items[] = $this->utils->buildNewItem("Pack1", "1.0.0.0");
        $items[] = $this->utils->buildNewItem("Pack2", "1.0.0.0");
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $query = "id eq 'Pack1'";

        $target = new FileDbStorage($properties, $queryParser, $items);
        $target->initialize(array(), [new FileNugetVersionType()], new NugetPackage());

        $result = $target->query($query, -1, 0);

        $this->assertEquals(1, sizeof($result));
        $this->assertEquals("Pack1", $result[0]->Id);
    }

}