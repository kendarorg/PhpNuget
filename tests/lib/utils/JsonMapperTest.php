<?php

namespace lib\utils;

use lib\nuget\models\NugetDependency;
use lib\nuget\models\NugetPackage;
use PHPUnit\Framework\TestCase;

class JsonMapperTest extends TestCase
{

    public function testDeserialization(): void
    {
        $objectInstance = new NugetPackage();
        $jsonData = '{"Id":"test","Author":["a","b"],"Dependencies":[{"Id":"dep"}]}';
        $mapper = new JsonMapper();

        $result = $mapper->map(
            json_decode($jsonData),
            $objectInstance
        );
        $this->assertEquals(1,sizeof($result->Dependencies));
        $this->assertTrue($result->Dependencies[0] instanceof NugetDependency);
        $this->assertEquals(2,sizeof($result->Author));
        $this->assertEquals("a",$result->Author[0]);
    }

}