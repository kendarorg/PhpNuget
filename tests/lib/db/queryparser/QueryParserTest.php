<?php

namespace lib\db\queryparser;

use lib\db\QueryParser;
use lib\db\TestObject;
use lib\nuget\NugetVersionType;
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

        $result = $target->parse($query,new TestObject());
        $ser = json_encode($result);
        $this->assertEquals(
            '[{"Type":"function","Value":"doand","Id":null,"Children":['.
                '{"Type":"function","Value":"doeq","Id":null,"Children":['.
                    '{"Type":"field","Value":"Id","Id":null,"Children":[]},'.
                    '{"Type":"string","Value":"ID","Id":null,"Children":[]}]},'.
                '{"Type":"function","Value":"doeq","Id":null,"Children":['.
                    '{"Type":"field","Value":"Listed","Id":null,"Children":[]},'.
                    '{"Type":"boolean","Value":true,"Id":null,"Children":[]}]}]}]',
            $ser);
    }


    public function testNestedParse(): void
    {

        $target = new QueryParser();
        $query = "Id eq 'ID' and ( Listed eq true or Added neq 'test') orderby Id asc,Version asc";

        $result = $target->parse($query,new TestObject());
        $ser = json_encode($result);
        $this->assertEquals(
            '[{"Type":"function","Value":"doand","Id":null,"Children":['.
                '{"Type":"function","Value":"doeq","Id":null,"Children":['.
                    '{"Type":"field","Value":"Id","Id":null,"Children":[]},'.
                    '{"Type":"string","Value":"ID","Id":null,"Children":[]}]},'.
                    '{"Type":"function","Value":"door","Id":null,"Children":['.
                        '{"Type":"function","Value":"doeq","Id":null,"Children":['.
                            '{"Type":"field","Value":"Listed","Id":null,"Children":[]},'.
                            '{"Type":"boolean","Value":true,"Id":null,"Children":[]}]},'.
                        '{"Type":"function","Value":"doneq","Id":null,"Children":['.
                            '{"Type":"field","Value":"Added","Id":null,"Children":[]},'.
                            '{"Type":"string","Value":"test","Id":null,"Children":[]}]}]}]}]',
            $ser);
    }

    public function testInvertedParse(): void
    {

        $target = new QueryParser();
        $query = "22 eq Id";

        $result = $target->parse($query,new TestObject());
        $ser = json_encode($result);
        $this->assertEquals(
            '[{"Type":"function","Value":"doeq","Id":null,"Children":[{"Type":"number","Value":22,"Id":null,"Children":[]},{"Type":"field","Value":"Id","Id":null,"Children":[]}]}]',
            $ser);
    }

    public function testUnknownType(): void
    {

        $target = new QueryParser();
        $query = "22.77.5 eq Id";

        $result = $target->parse($query,new TestObject(),[new NugetVersionType()]);
        $ser = json_encode($result);
        $this->assertEquals(
            '[{"Type":"function","Value":"doeq","Id":null,"Children":[{"Type":"version","Value":"22.77.5","Id":null,"Children":[]},{"Type":"field","Value":"Id","Id":null,"Children":[]}]}]',
            $ser);
    }
}
