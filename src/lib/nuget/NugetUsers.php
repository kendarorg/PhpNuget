<?php

namespace lib\nuget;

use lib\db\DbStorage;
use lib\nuget\models\NugetUser;

class NugetUsers extends BaseDb
{

    /**
     * @param DbStorage $dbStorage
     */
    public function __construct($dbStorage)
    {
        parent::__construct($dbStorage,"users");
    }
}