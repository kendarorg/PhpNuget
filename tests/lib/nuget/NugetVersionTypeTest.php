<?php

namespace lib\nuget;

use lib\db\parser\InternalTypeBuilder;
use PHPUnit\Framework\TestCase;

class NugetVersionTypeTest extends TestCase
{
    public function testGt()
    {
        $target = new NugetVersionType();
        $result = $target->dogt([
            InternalTypeBuilder::buildItem("1.1","version","id"),
            InternalTypeBuilder::buildItem("1.0","version","id")]);
        $this->assertTrue($result->Value);
    }
    public function testLt()
    {
        $target = new NugetVersionType();
        $result = $target->dolt([
            InternalTypeBuilder::buildItem("1.1","version","id"),
            InternalTypeBuilder::buildItem("1.0","version","id")]);
        $this->assertFalse($result->Value);
    }
}