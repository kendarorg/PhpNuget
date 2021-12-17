<?php

namespace lib\utils;

class StringUtils
{
    /**
     * @param string $value
     * @return bool
     */
    public static function isNullOrEmpty($value){
        if($value == null || !is_string($value) || strlen($value)==0) return true;
        $value = trim($value);
        return strlen($value)==0;
    }

    public static function timeToIso8601Date($time=false) {
        if(!$time) $time=time();
        return date("Y-m-d", $time) . 'T' . date("H:i:s", $time) .'.000000Z';
    }

    public static function specialChars($hasMap)
    {
        if(is_array($hasMap)) {
            foreach ($hasMap as $key => $value) {
                if (!is_array($value)) {
                    $hasMap[$key] = trim(htmlspecialchars($value));
                } else {
                    //TODO: Special chars deep
                    $hasMap[$key] = $value;
                    //$hasMap[$key] = array();
                    //for($i=0;$i<sizeof($value);$i++){
                    //$hasMap[$key][] = htmlspecialchars($value[$i]);
                    //}
                }
            }
            return $hasMap;
        }else{
            return trim(htmlspecialchars($hasMap));
        }
    }

    public static function indexOf($mystring, $findme)
    {
        $pos = strpos($mystring, $findme);
        if ($pos === false) {
            return -1;
        } else {
            return $pos;
        }
    }
}