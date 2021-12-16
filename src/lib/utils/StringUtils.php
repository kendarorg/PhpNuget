<?php

namespace lib\utils;

class StringUtils
{
    /**
     * @param string $value
     * @return bool
     */
    public static function isNullOrEmpty($value){
        if($value == null || strlen($value)==0) return true;
        $value = trim($value);
        return strlen($value)==0;
    }
}