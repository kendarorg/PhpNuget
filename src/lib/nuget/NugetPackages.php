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
        $keys = ["Id", "Version"];
        $extraTypes = [new NugetVersionType()];
        $object = new NugetPackage();
        parent::__construct($dbStorage, "packages", $keys, $extraTypes, $object);
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
            $this->postQuery($data);
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
    public function queryAndCount($query, $limit = -1, $skip = 0, &$count)
    {
        $data = parent::queryAndCount($query, $limit, $skip, $count);
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
        if (ends_with(strtolower($row->IconUrl), strtolower("packagedefaulticon-50x50.png"))) {
            $row->IconUrl = UrlUtils::CurrentUrl(Settings::$SiteRoot . "content/packagedefaulticon-50x50.png");
        }
    }
}