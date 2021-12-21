<?php

namespace lib\nuget;

use lib\db\parser\InternalTypeBuilder;
use lib\nuget\fields\file\NugetVersionType;
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

    public function testGtPre1()
    {
        $target = new NugetVersionType();
        $result = $target->dolt([
            InternalTypeBuilder::buildItem("1.0.0.1-alpha","version","id"),
            InternalTypeBuilder::buildItem("1.0.0.1-beta","version","id")]);
        $this->assertTrue($result->Value);
    }
    public function testGtPre2()
    {
        $target = new NugetVersionType();
        $result = $target->dolt([
            InternalTypeBuilder::buildItem("1.0.0.1-alpha","version","id"),
            InternalTypeBuilder::buildItem("1.0.0.1-pre-alpha","version","id")]);
        $this->assertTrue($result->Value);
    }
    public function testGtPre3()
    {
        $target = new NugetVersionType();
        $result = $target->dolt([
            InternalTypeBuilder::buildItem("1.0.0.1-alpha","version","id"),
            InternalTypeBuilder::buildItem("1.0.0.1-preAlpha","version","id")]);
        $this->assertTrue($result->Value);
    }
    public function testLtPre4()
    {
        $target = new NugetVersionType();
        $result = $target->dogt([
            InternalTypeBuilder::buildItem("1.0.0.1-preBeta","version","id"),
            InternalTypeBuilder::buildItem("1.0.0.1-preAlpha","version","id")]);
        $this->assertTrue($result->Value);
    }
    public function testSemantic()
    {
        $target = new NugetVersionType();
        $result = $target->dolt([
            InternalTypeBuilder::buildItem("1.0.0.1-alpha","version","id"),
            InternalTypeBuilder::buildItem("1.0.0.1","version","id")]);
        $this->assertTrue($result->Value);
    }
}