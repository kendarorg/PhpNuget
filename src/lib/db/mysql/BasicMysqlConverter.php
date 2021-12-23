<?php

namespace lib\db\mysql;

use lib\nuget\models\NugetPackage;
use lib\utils\JsonMapper;
use ReflectionClass;
use ReflectionProperty;

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

    public function fromAssoc($data,$result)
    {
        $reflect = new ReflectionClass($result);
        $vars = $reflect->getProperties(ReflectionProperty::IS_PRIVATE ||ReflectionProperty::IS_PUBLIC || ReflectionProperty::IS_PROTECTED);
        $mapper = new JsonMapper();

        foreach ($vars as $privateVar) {
            $key = $privateVar->getName();
            if(!isset($data[$key]))continue;
            $value = $data[$key];
            if (in_array($key, $this->arrays)) {
                $result->$key = json_decode($value);
            } else if (isset( $this->objects[$key])) {
                $data = json_decode($value);
                if(is_array($data)){
                    $partial = [];
                    foreach ($data as $dt){
                        $partial[]= $mapper->map(
                            $dt,
                            $this->objects[$key]
                        );
                    }
                    $result->$key = $partial;
                }else {
                    $result->$key = $mapper->map(
                        $data,
                        $this->objects[$key]
                    );
                }
            } else {
                $result->$key = $value;
            }
        }
        return $result;
    }

    public function toAssoc($data)
    {
        $reflect = new ReflectionClass($data);
        $vars = $reflect->getProperties(ReflectionProperty::IS_PRIVATE ||ReflectionProperty::IS_PUBLIC || ReflectionProperty::IS_PROTECTED);

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
        $this->extraAssoc($result,$data);
        return $result;
    }

    public function extraAssoc(&$result,$data){

    }
}