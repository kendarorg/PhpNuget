<?php

namespace lib\nuget;

use lib\db\BaseDb;
use lib\db\DbStorage;
use lib\nuget\models\NugetUser;

class NugetUsers extends BaseDb
{

    /**
     * @param DbStorage $dbStorage
     */
    public function __construct($dbStorage)
    {
        $keys=array();
        $extraTypes = array();
        $object = new NugetUser();
        parent::__construct($dbStorage,"packages", $keys,$extraTypes,$object);
    }
}