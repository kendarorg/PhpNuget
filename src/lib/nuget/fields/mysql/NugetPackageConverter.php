<?php

namespace lib\nuget\fields\mysql;

use lib\nuget\models\NugetDependency;
use lib\nuget\models\NugetPackage;
use lib\utils\JsonMapper;
use ReflectionClass;
use ReflectionProperty;

class NugetPackageConverter extends BasicMysqlConverter
{
    public function __construct()
    {
        $this->objects = ["Dependencies" => new NugetDependency()];
        $this->arrays =  ["Author", "Owners", "References"];
    }


}