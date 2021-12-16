<?php

namespace lib\nuget;

use lib\db\BaseDb;
use lib\db\DbStorage;

class NugetPackages extends BaseDb
{
    /**
     * @param DbStorage $dbStorage
     */
    public function __construct($dbStorage)
    {
        parent::__construct($dbStorage,"packages");
    }
}