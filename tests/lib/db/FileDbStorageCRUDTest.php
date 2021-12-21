<?php

namespace lib\db;

use lib\db\file\FileDbStorage;
use lib\nuget\models\NugetPackage;
use lib\nuget\NugetVersionType;
use lib\utils\Properties;

class FileDbStorageCRUDTest
{
    private FileDbStorageTestUtils $utils;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->utils = new FileDbStorageTestUtils();
    }

    public function testShouldAddData(){
        $items = array();
        $queryParser = new QueryParser();
        $properties = new Properties(null);
        $properties->setProperty();
        $query = "Id eq 'Pack1'";

        $target = new FileDbStorage($properties, $queryParser, $items);
        $target->initialize(array(), [new NugetVersionType()], new NugetPackage());

        $result = $target->query($query, -1, 0);
    }
}