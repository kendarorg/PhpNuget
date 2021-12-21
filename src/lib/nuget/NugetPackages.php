<?php

namespace lib\nuget;

use lib\db\BaseDb;
use lib\db\DbStorage;
use lib\nuget\models\NugetPackage;

class NugetPackages extends BaseDb
{
    /**
     * @param DbStorage $dbStorage
     */
    public function __construct($dbStorage)
    {
        $keys=array();
        $extraTypes = [new NugetVersionType()];
        $object = new NugetPackage();
        parent::__construct($dbStorage,"packages", $keys,$extraTypes,$object);
    }
}