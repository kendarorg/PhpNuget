<?php

namespace lib\utils;

class ArrayUtils
{
    /**
     * @param array $srcMap
     * @param string $srcKey
     * @param mixed $default
     * @return mixed
     */
    public static function getIfExists(&$srcMap, $srcKey, $default =null)
    {
        if (array_key_exists($srcKey, $srcMap)) {
            return $srcMap[$srcKey];
        }
        return $default;
    }

    /**
     * @param array $dstMap
     * @param array $srcMap
     * @return void
     */
    public static function mergeLowerCase(&$dstMap,&$srcMap){
        foreach ($srcMap as $key=>$value){
            $dstMap[strtolower($key)]=$value;
        }
    }
}