<?php

namespace lib\nuget;

use lib\db\file\MySqlDbStorage;
use lib\db\mysql\MySqlMock;
use lib\db\QueryParser;
use lib\nuget\fields\mysql\NugetPackageConverter;
use lib\utils\Properties;
use PHPUnit\Framework\TestCase;

class MySqlNugetPackagesTest extends TestCase
{
    private function setupDb()
    {
        $this->properties = new Properties(null);
        $this->mysqli = new MySqlMock();
        $this->queryParser = new QueryParser();
        $this->converter = new NugetPackageConverter();
        $this->mysqlDbStorage = new MySqlDbStorage($this->properties, $this->queryParser,
            null, $this->mysqli, $this->converter);
        $this->target = new NugetPackages($this->mysqlDbStorage,$this->properties);
    }

    public function testEmptyResult()
    {
        $this->setupDb();
        $this->mysqli->initialize(array());
        $result = $this->target->query("Id eq 'test'");
        $this->assertNotNull($result);
        $this->assertEquals(0, sizeof($result));
    }

    public function testEmptyCount()
    {
        $this->setupDb();
        $data = array();
        $data[] = ['countResult'=>0];
        $this->mysqli->initialize($data);
        $result = $this->target->count("Id eq 'test'");
        $this->assertEquals(0, $result);
    }

    public function testEmptyCountAndQuery()
    {
        $this->setupDb();
        $data = array();
        $data[] = ['countResult'=>0];
        $this->mysqli->initialize($data);
        $this->mysqli->initialize(array());
        $count = -1;
        $result = $this->target->queryAndCount("Id eq 'test'",$count);
        $this->assertEquals(0, sizeof($result));
        $this->assertEquals(0, $count);
    }
}