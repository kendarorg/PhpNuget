<?php

namespace lib\nuget;

use lib\db\BaseDb;
use lib\db\DbStorage;
use lib\nuget\fields\file\FileArraysCompositeField;
use lib\nuget\fields\file\FileDependencyCompositeField;
use lib\nuget\fields\file\FileNugetVersionType;
use lib\nuget\fields\file\MySqlNugetVersionType;
use lib\nuget\models\NugetPackage;
use lib\utils\HttpUtils;
use lib\utils\StringUtils;

class NugetPackages extends BaseDb
{
    private $properties;

    /**
     * @param DbStorage $dbStorage
     */
    public function __construct($dbStorage,$properties)
    {
        $keys = ["Id", "Version"];
        $dbType = $properties->getProperty("dbType","file");
        $extraTypes = [new FileNugetVersionType(), new FileArraysCompositeField(), new FileDependencyCompositeField()];
        if($dbType=="mysql"){
            $extraTypes = [new MySqlNugetVersionType()];
        }

        $object = new NugetPackage();
        parent::__construct($dbStorage, "packages", $keys, $extraTypes, $object);
        $this->properties = $properties;
    }

    /**
     * @param string $query
     * @param integer $limit
     * @param integer $skip
     * @return array
     */
    public function query($query, $limit = -1, $skip = 0)
    {
        $data = parent::query($query, $limit, $skip);
        for ($i = 0; $i < sizeof($data); $i++) {
            $this->postQuery($data[$i]);
        }
        return $data;
    }

    /**
     * @param string $query
     * @param integer $count
     * @param integer $limit
     * @param integer $skip
     * @return array
     */
    public function queryAndCount($query, &$count, $limit = -1, $skip = 0)
    {
        $data = parent::queryAndCount($query, $count, $limit, $skip);
        for ($i = 0; $i < sizeof($data); $i++) {
            $this->postQuery($data[$i]);
        }
        return $data;
    }

    /**
     * @return mixed
     */
    public function getByKey()
    {
        $data = parent::getByKey(func_get_args());
        if ($data == null) return null;
        $this->postQuery($data);
        return $data;
    }

    private function postQuery(&$row)
    {
        if (StringUtils::endsWith(strtolower($row->IconUrl), strtolower("packagedefaulticon-50x50.png"))) {
            $row->IconUrl = HttpUtils::currentUrl("content/packagedefaulticon-50x50.png",$this->properties);
        }
    }
}