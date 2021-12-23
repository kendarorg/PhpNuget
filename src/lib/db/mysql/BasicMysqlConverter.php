<?php

namespace lib\db\mysql;

use lib\nuget\models\NugetPackage;
use lib\utils\JsonMapper;

class BasicMysqlConverter
{
    /**
     * @var string[]
     */
    protected array $objects = [];
    /**
     * @var string[]
     */
    protected array $arrays =[];

    public function fromAssoc($data)
    {
        $result = new NugetPackage();
        $mapper = new JsonMapper();

        foreach ($data as $key => $value) {
            if (in_array($key, $this->arrays)) {
                $result->$key = json_decode($value);
            } else if (isset( $this->objects[$key])) {
                $result->$key = $mapper->map(
                    json_decode($value),
                    $this->objects[$key]
                );
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
            if (in_array($key, $this->arrays) || isset( $this->objects[$key])) {
                $result[$key] = json_encode($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}