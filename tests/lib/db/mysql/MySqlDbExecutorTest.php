<?php

namespace lib\db\mysql;

use lib\db\file\FileDbExecutor;
use lib\db\QueryParser;
use lib\db\TestObject;
use PHPUnit\Framework\TestCase;

class MySqlDbExecutorTest  extends TestCase
{
    public function testMatch(): void
    {
        $target = new QueryParser();
        $query = "Id eq 'TEST'";
        $item = new TestObject();
        $target->parse($query, $item);
        $item->Id = "TEST";
        $executor = $target->setupExecutor(new MySqlDbExecutor());
        $result = $executor->execute($item);
        $this->assertNotNull($result);
        $this->assertEquals("",$result);
    }
}