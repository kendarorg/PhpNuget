<?php

namespace lib\nuget\fields\mysql;

use lib\nuget\models\NugetPackage;
use ReflectionClass;
use ReflectionProperty;

class NugetPackageConverter
{
    /**
     * @var string[]
     */
    private array $objects = ["Dependencies" => "func"];
    /**
     * @var string[]
     */
    private array $arrays = ["Author", "Owners", "References"];

    public function fromAssoc($data)
    {
        $result = new NugetPackage();
        foreach ($data as $key => $value) {
            if (in_array($key, $this->arrays)) {

            } else if (in_array($key, $this->objects)) {

            } else {
                $result->$key = $value;
            }
        }
        return $result;
    }

    public function toAssoc($data)
    {
        $reflect = new ReflectionClass($data);
        $vars = $reflect->getProperties(ReflectionProperty::IS_PRIVATE || ReflectionProperty::IS_PROTECTED);

        $result = array();
        foreach ($vars as $privateVar) {
            $key = $privateVar->getName();
            $value = $privateVar->getValue($data);
            if (in_array($key, $this->arrays)) {

            } else if (in_array($key, $this->objects)) {

            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}