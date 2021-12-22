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
        
        $executor = $target->setupExecutor(new MySqlDbExecutor());
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        $this->assertEquals("Version='1.0.0.0'",$result);
    }

    public function testVersionCompareGte()
    {
        $target = new QueryParser();
        $query = "Version gte '1.0.0.1'";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);
        
        $executor = $target->setupExecutor(new MySqlDbExecutor());
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        $this->assertEquals("SEMVER_GTE(Version,'1.0.0.1')",$result);
    }

    public function testVersionCompareLte()
    {
        $target = new QueryParser();
        $query = "Version lte '1.0.0.1'";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);
        
        $executor = $target->setupExecutor(new MySqlDbExecutor());
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        $this->assertEquals("(SEMVER_LT(Version,'1.0.0.1') OR Version='1.0.0.1')",$result);
    }

    public function testVersionCompareLt()
    {
        $target = new QueryParser();
        $query = "Version lt '1.0.0.1'";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);

        $executor = $target->setupExecutor(new MySqlDbExecutor());
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        $this->assertEquals("SEMVER_LT(Version,'1.0.0.1')",$result);
    }

    public function testVersionCompareGt()
    {
        $target = new QueryParser();
        $query = "Version gt '1.0.0.1'";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);

        $executor = $target->setupExecutor(new MySqlDbExecutor());
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        $this->assertEquals("(SEMVER_GTE(Version,'1.0.0.1') AND Version!='1.0.0.1')",$result);
    }

    public function testVersionCompareEq()
    {
        $target = new QueryParser();
        $query = "Version eq '1.0.0.1'";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);

        $executor = $target->setupExecutor(new MySqlDbExecutor());
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        $this->assertEquals("Version='1.0.0.1'",$result);
    }

    public function testVersionCompareNeq()
    {
        $target = new QueryParser();
        $query = "Version neq '1.0.0.1'";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);

        $executor = $target->setupExecutor(new MySqlDbExecutor());
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        $this->assertEquals("Version!='1.0.0.1'",$result);
    }

    public function testVersionOrder()
    {
        $target = new QueryParser();
        $query = "orderby version asc";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);

        $executor = $target->setupExecutor(new MySqlDbExecutor());
        $result = $executor->execute($item);
        $orderBy = $executor->doSort($toSort);
        $this->assertNotNull($orderBy);
        $this->assertEquals("Version0 ASC, Version1 ASC, Version2 ASC, Version3 ASC, VersionBeta ASC",$orderBy);
    }

    public function testGroupBy()
    {
        $target = new QueryParser();
        $query = "Groupby Id";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);
        $toSort = array();
        $executor = $target->setupExecutor(new MySqlDbExecutor());
        $result = $executor->execute($item);
        $orderBy = $executor->doGroupBy($toSort);
        $this->assertNotNull($orderBy);
        $this->assertEquals("Id",$orderBy);
    }
}