<?php

namespace lib\db\queryparser;

use lib\db\QueryParser;
use lib\db\TestObject;
use PHPUnit\Framework\TestCase;

class QueryExecuteTest extends TestCase
{
    public function testMatch(): void
    {
        $target = new QueryParser();
        $query = "Id eq 'TEST'";
        $item = new TestObject();
        $target->parse($query, $item);
        $item->Id = "TEST";
        $this->assertTrue($target->execute($item));
    }

    public function testNoMatch(): void
    {
        $target = new QueryParser();
        $query = "Id eq 'TEST'";
        $item = new TestObject();
        $target->parse($query, $item);
        $item->Id = "NONE";
        $this->assertFalse($target->execute($item));
    }

    public function testComplex(): void
    {
        $target = new QueryParser();
        $query = "Id eq 'NONE' or (Added eq true and Id eq 'TEST')";
        $item = new TestObject();
        $target->parse($query, $item);
        $item->Id = "TEST";
        $item->Added =true;
        $item->Listed = false;
        $this->assertTrue($target->execute($item));
    }
}
