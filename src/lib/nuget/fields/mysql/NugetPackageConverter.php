<?php

namespace lib\nuget\fields\mysql;

use lib\db\mysql\BasicMysqlConverter;
use lib\nuget\models\NugetDependency;

class NugetPackageConverter extends BasicMysqlConverter
{
    public function __construct()
    {
        $this->objects = ["Dependencies" => new NugetDependency()];
        $this->arrays =  ["Author", "Owners", "References"];
    }


}