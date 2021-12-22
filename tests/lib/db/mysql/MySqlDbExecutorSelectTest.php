<?php

namespace lib\db\mysql;

use lib\db\QueryParser;
use lib\db\TestObject;
use lib\nuget\fields\file\MySqlNugetVersionType;
use PHPUnit\Framework\TestCase;

class MySqlDbExecutorSelectTest  extends TestCase
{
    private $mysqli;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->mysqli = new class {
            public function real_escape_string($msg)
            {
                return str_replace("'","", $msg);
            }
        };
    }

    public function testBasic(): void
    {
        $target = new QueryParser();
        $query = "Id eq 'Pack1'";
        $item = new TestObject();
        $target->parse($query, $item);
        
        $executor = $target->setupExecutor(new MySqlDbExecutor($this->mysqli));
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        $this->assertEquals(" WHERE Id='Pack1'",$result);
    }

    public function testWithParenthesis(): void
    {
        $target = new QueryParser();
        $query = "(Id eq 'Pack1') and true";
        $item = new TestObject();
        $target->parse($query, $item);
        
        $executor = $target->setupExecutor(new MySqlDbExecutor($this->mysqli));
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        $this->assertEquals(" WHERE (Id='Pack1' and true)",$result);
    }

    public function testVersion(): void
    {
        $target = new QueryParser();
        $query = "Version eq '1.0.0.0'";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);
        
        $executor = $target->setupExecutor(new MySqlDbExecutor($this->mysqli));
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        $this->assertEquals(" WHERE Version='1.0.0.0'",$result);
    }

    public function testVersionCompareGte()
    {
        $target = new QueryParser();
        $query = "Version gte '1.0.0.1'";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);
        
        $executor = $target->setupExecutor(new MySqlDbExecutor($this->mysqli));
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        //$this->assertEquals("SEMVER_GTE(Version,'1.0.0.1')",$result);
        $this->assertEquals(" WHERE (Version0>=1) OR (Version0=1 AND Version1>=0) OR (Version0=1 AND Version1=0 AND Version2>=0) OR (Version0=1 AND Version1=0 AND Version2=0 AND Version3>=1) OR (Version0=1 AND Version1=0 AND Version2=0 AND Version3=1AND LENGTH(VersionBeta)<=LENGTH('') AND VersionBeta>='')",
            $result);
    }

    public function testVersionCompareLte()
    {
        $target = new QueryParser();
        $query = "Version lte '1.0.0.1'";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);
        
        $executor = $target->setupExecutor(new MySqlDbExecutor($this->mysqli));
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        //$this->assertEquals("(SEMVER_LT(Version,'1.0.0.1') OR Version='1.0.0.1')",$result);
        $this->assertEquals(" WHERE (Version0<=1) OR (Version0=1 AND Version1<=0) OR (Version0=1 AND Version1=0 AND Version2<=0) OR (Version0=1 AND Version1=0 AND Version2=0 AND Version3<=1) OR (Version0=1 AND Version1=0 AND Version2=0 AND Version3=1AND LENGTH(VersionBeta)>=LENGTH('') AND VersionBeta<='')",
            $result);

    }

    public function testVersionCompareLt()
    {
        $target = new QueryParser();
        $query = "Version lt '1.0.0.1'";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);

        $executor = $target->setupExecutor(new MySqlDbExecutor($this->mysqli));
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        //$this->assertEquals("SEMVER_LT(Version,'1.0.0.1')",$result);
        $this->assertEquals(" WHERE (Version0<1) OR (Version0=1 AND Version1<0) OR (Version0=1 AND Version1=0 AND Version2<0) OR (Version0=1 AND Version1=0 AND Version2=0 AND Version3<1) OR (Version0=1 AND Version1=0 AND Version2=0 AND Version3=1AND LENGTH(VersionBeta)>LENGTH('') AND VersionBeta<'')",
            $result);
    }

    public function testVersionCompareGt()
    {
        $target = new QueryParser();
        $query = "Version gt '1.0.0.1'";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);

        $executor = $target->setupExecutor(new MySqlDbExecutor($this->mysqli));
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        //$this->assertEquals("(SEMVER_GTE(Version,'1.0.0.1') AND Version!='1.0.0.1')",$result);
        $this->assertEquals(" WHERE (Version0>1) OR (Version0=1 AND Version1>0) OR (Version0=1 AND Version1=0 AND Version2>0) OR (Version0=1 AND Version1=0 AND Version2=0 AND Version3>1) OR (Version0=1 AND Version1=0 AND Version2=0 AND Version3=1AND LENGTH(VersionBeta)<LENGTH('') AND VersionBeta>'')",
            $result);


    }

    public function testVersionCompareEq()
    {
        $target = new QueryParser();
        $query = "Version eq '1.0.0.1'";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);

        $executor = $target->setupExecutor(new MySqlDbExecutor($this->mysqli));
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        $this->assertEquals(" WHERE Version='1.0.0.1'",$result);
    }

    public function testVersionCompareNeq()
    {
        $target = new QueryParser();
        $query = "Version neq '1.0.0.1'";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);

        $executor = $target->setupExecutor(new MySqlDbExecutor($this->mysqli));
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        $this->assertEquals(" WHERE Version!='1.0.0.1'",$result);
    }

    public function testVersionOrder()
    {
        $target = new QueryParser();
        $query = "orderby version asc";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);

        $executor = $target->setupExecutor(new MySqlDbExecutor($this->mysqli));
        $orderBy = $executor->doSort($toSort);
        $this->assertNotNull($orderBy);
        $this->assertEquals(" ORDER BY Version0 ASC, Version1 ASC, Version2 ASC, Version3 ASC, LENGTH(VersionBeta) ASC, VersionBeta ASC",$orderBy);
    }

    public function testGroupBy()
    {
        $target = new QueryParser();
        $query = "Groupby Id";
        $item = new TestObject();
        $target->parse($query, $item,[new MySqlNugetVersionType()]);
        $toSort = array();
        $executor = $target->setupExecutor(new MySqlDbExecutor($this->mysqli));
        $orderBy = $executor->doGroupBy($toSort);
        $this->assertNotNull($orderBy);
        $this->assertEquals(" GROUP BY Id",$orderBy);
    }
}