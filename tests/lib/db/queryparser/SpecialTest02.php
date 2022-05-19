<?php

namespace lib\db\queryparser;

use lib\db\file\FileDbStorageTestUtils;
use lib\db\QueryParser;
use lib\db\TestObject;
use PHPUnit\Framework\TestCase;

class SpecialTest02 extends TestCase
{
    private FileDbStorageTestUtils $utils;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->utils = new FileDbStorageTestUtils();
    }

    public function testStringEqField(){
        $target = new QueryParser();
        $query = "'test' eq Field";

        $result = $target->parse($query,new TestObject());
        $ser = json_encode($result);
        $this->assertEquals(
            '[{"Type":"function","Value":"doeq","Id":null,"Children":[{"Type":"string","Value":"test","Id":null,"Children":[]},{"Type":"field","Value":"Field","Id":null,"Children":[]}]}]',
            $ser);
    }

    public function testSubstring(){
        $target = new QueryParser();
        $query = "substringof('test', Field)";

        $result = $target->parse($query,new TestObject());
        $ser = json_encode($result);
        $this->assertEquals(
            '[{"Type":"function","Value":"substringof","Id":null,"Children":[{"Type":"string","Value":"test","Id":null,"Children":[]},{"Type":"field","Value":"Field","Id":null,"Children":[]}]}]',
            $ser);
    }

    public function testSubstringEquality(){
        $target = new QueryParser();
        $query = "substringof('test', Field) eq true";

        $result = $target->parse($query,new TestObject());
        $ser = json_encode($result);
        $this->assertEquals(
            '[{"Type":"function","Value":"doeq","Id":null,"Children":[{"Type":"function","Value":"substringof","Id":null,"Children":[{"Type":"string","Value":"test","Id":null,"Children":[]},{"Type":"field","Value":"Field","Id":null,"Children":[]}]},{"Type":"boolean","Value":true,"Id":null,"Children":[]}]}]',
            $ser);
    }

    public function testParenthesis(){
        $target = new QueryParser();
        $query = "(true and Third) eq true";

        $result = $target->parse($query,new TestObject());
        $ser = json_encode($result);
        $this->assertEquals(
            '[{"Type":"function","Value":"doeq","Id":null,"Children":[{"Type":"function","Value":"doand","Id":null,"Children":[{"Type":"boolean","Value":true,"Id":null,"Children":[]},{"Type":"field","Value":"Third","Id":null,"Children":[]}]},{"Type":"boolean","Value":true,"Id":null,"Children":[]}]}]',
            $ser);
    }

    public function testParenthesis2(){
        $target = new QueryParser();
        $query = "('test' eq Field)";

        $result = $target->parse($query,new TestObject());
        $ser = json_encode($result);
        $this->assertEquals(
            '[{"Type":"function","Value":"doeq","Id":null,"Children":[{"Type":"string","Value":"test","Id":null,"Children":[]},{"Type":"field","Value":"Field","Id":null,"Children":[]}]}]',
            $ser);
    }

}