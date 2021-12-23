<?php

namespace lib\nuget\fields\mysql;

use lib\db\mysql\BasicMysqlConverter;
use lib\nuget\models\NugetDependency;
use lib\nuget\NugetUtils;

class NugetPackageConverter extends BasicMysqlConverter
{
    public function __construct()
    {
        $this->objects = ["Dependencies" => new NugetDependency()];
        $this->arrays =  ["Author", "Owners", "References"];
    }

    public function extraAssoc(&$result,$data){
        $spl = NugetUtils::buildSplitVersion($data->Version);
        $result["Version0"] = $spl[0];
        $result["Version1"] = $spl[1];
        $result["Version2"] = $spl[2];
        $result["Version3"] = $spl[3];
        $result["VersionBeta"] = $spl[4];
    }
}