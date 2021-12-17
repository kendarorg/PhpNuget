<?php

namespace lib\db;

use PHPUnit\Framework\TestCase;

class QueryParserTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        //Small couple of lines of my code.
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
    public function testSimpleTokenize(): void
    {

        $target = new QueryParser();
        $query = "Id eq 'ID' and Listed eq true orderby Id asc,Version asc";

        $result = $target->tokenize($query);
        $ser = json_encode($result);
        $this->assertEquals(
            '["Id","eq","\'ID\'","and","Listed","eq","true","orderby","Id","asc","Version","asc"]',
            $ser);
    }
    public function testSimpleParse(): void
    {

        $target = new QueryParser();
        $query = "Id eq 'ID' and Listed eq true orderby Id asc,Version asc";

        $result = $target->parse($query);
        $ser = json_encode($result);
        $this->assertEquals(
            '[{"Type":"function","Value":"doand","Id":null,"Children":[{"Type":"function","Value":"doeq","Id":null,"Children":[{"Type":"field","Value":"Id","Id":null,"Children":[]},{"Type":"string","Value":"ID","Id":null,"Children":[]}]},{"Type":"function","Value":"doeq","Id":null,"Children":[{"Type":"field","Value":"Listed","Id":null,"Children":[]},{"Type":"boolean","Value":true,"Id":null,"Children":[]}]}]}]',
            $ser);
    }
}
