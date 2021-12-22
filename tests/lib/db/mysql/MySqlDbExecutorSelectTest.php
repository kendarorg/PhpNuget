<?php

namespace lib\db\mysql;

use lib\db\file\FileDbExecutor;
use lib\db\file\FileDbStorage;
use lib\db\QueryParser;
use lib\db\TestObject;
use lib\nuget\fields\file\FileNugetVersionType;
use lib\nuget\fields\file\MySqlNugetVersionType;
use lib\nuget\models\NugetPackage;
use lib\utils\Properties;
use PHPUnit\Framework\TestCase;

class MySqlDbExecutorSelectTest  extends TestCase
{
    public function testBasic(): void
    {
        $target = new QueryParser();
        $query = "Id eq 'Pack1'";
        $item = new TestObject();
        $target->parse($query, $item);
        $item->Id = "TEST";
        $executor = $target->setupExecutor(new MySqlDbExecutor());
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        $this->assertEquals("Id='Pack1'",$result);
    }

    public function testWithParenthesis(): void
    {
        $target = new QueryParser();
        $query = "(Id eq 'Pack1') and true";
        $item = new TestObject();
        $target->parse($query, $item);
        $item->Id = "TEST";
        $executor = $target->setupExecutor(new MySqlDbExecutor());
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        $this->assertEquals("(Id='Pack1' and true)",$result);
    }

    public function testVersion(): void
    {
        $target = new QueryParser();
        $query = "Version eq '1.0.0.0'";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);
        $item->Id = "TEST";
        $executor = $target->setupExecutor(new MySqlDbExecutor());
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        $this->assertEquals("(Id='Pack1' and true)",$result);
    }
}