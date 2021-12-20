<?php

namespace lib\db\queryparser;

use lib\db\FileDbStorageTestUtils;
use lib\db\QueryParser;
use lib\db\TestObject;
use PHPUnit\Framework\TestCase;

class SpecialTest01 extends TestCase
{
    private FileDbStorageTestUtils $utils;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->utils = new FileDbStorageTestUtils();
    }

    public function testWithTrue(){
        $target = new QueryParser();
        $query = "Field eq true";

        $result = $target->parse($query,new TestObject());
        $ser = json_encode($result);
        $this->assertEquals(
            '[{"Type":"function","Value":"doeq","Id":null,"Children":[{"Type":"field","Value":"Field","Id":null,"Children":[]},{"Type":"boolean","Value":true,"Id":null,"Children":[]}]}]',
            $ser);
    }

    public function testWithFalseAndTrue(){
        $target = new QueryParser();
        $query = "false and true";

        $result = $target->parse($query,new TestObject());
        $ser = json_encode($result);
        $this->assertEquals(
            '[{"Type":"function","Value":"doand","Id":null,"Children":[{"Type":"boolean","Value":false,"Id":null,"Children":[]},{"Type":"boolean","Value":true,"Id":null,"Children":[]}]}]',
            $ser);
    }

    public function testWithFalseOrTrue(){
        $target = new QueryParser();
        $query = "false or true";

        $result = $target->parse($query,new TestObject());
        $ser = json_encode($result);
        $this->assertEquals(
            '[{"Type":"function","Value":"door","Id":null,"Children":[{"Type":"boolean","Value":false,"Id":null,"Children":[]},{"Type":"boolean","Value":true,"Id":null,"Children":[]}]}]',
            $ser);
    }

    public function testEqNeq(){
        $target = new QueryParser();
        $query = "Field eq true and Other neq false";

        $result = $target->parse($query,new TestObject());
        $ser = json_encode($result);
        $this->assertEquals(
            '[{"Type":"function","Value":"doand","Id":null,"Children":[{"Type":"function","Value":"doeq","Id":null,"Children":[{"Type":"field","Value":"Field","Id":null,"Children":[]},{"Type":"boolean","Value":true,"Id":null,"Children":[]}]},{"Type":"function","Value":"doneq","Id":null,"Children":[{"Type":"field","Value":"Other","Id":null,"Children":[]},{"Type":"boolean","Value":false,"Id":null,"Children":[]}]}]}]',
            $ser);
    }

    public function testNumber(){
        $target = new QueryParser();
        $query = "224 eq Field";

        $result = $target->parse($query,new TestObject());
        $ser = json_encode($result);
        $this->assertEquals(
            '[{"Type":"function","Value":"doeq","Id":null,"Children":[{"Type":"number","Value":224,"Id":null,"Children":[]},{"Type":"field","Value":"Field","Id":null,"Children":[]}]}]',
            $ser);
    }

}