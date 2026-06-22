<?php



class NugetUsers extends BaseDb
{

    /**
     * @param DbStorage $dbStorage
     */
    public function __construct($dbStorage)
    {
        $keys = ["UserId"];
        $extraTypes = array();
        $object = new NugetUser();
        parent::__construct($dbStorage, "users", $keys, $extraTypes, $object);
    }
}