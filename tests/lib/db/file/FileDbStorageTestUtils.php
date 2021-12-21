<?php

namespace lib\db\file;

use lib\nuget\models\NugetPackage;
use lib\nuget\NugetUtils;

class FileDbStorageTestUtils
{
    /**
     * @param string $id
     * @param string $version
     * @return NugetPackage
     */
    public function buildNewItem(string $id, string $version): NugetPackage
    {
        $utils = new NugetUtils();
        $result = new NugetPackage();
        $result->Id = $id;
        $result->Version = $version;
        $v = $utils->buildSplitVersion($version);
        $result->Version0 = $v[0];
        $result->Version1 = $v[1];
        $result->Version2 = $v[2];
        $result->Version3 = $v[3];
        $result->VersionBeta = $v[4];
        return $result;
    }
}