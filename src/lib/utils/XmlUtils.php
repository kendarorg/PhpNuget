<?php

namespace lib\utils;

class XmlUtils
{
    static function xml2ArrayGetKeyOrArray($xmlArray,$key)
    {
        $toret = array();
        if(array_key_exists($key,$xmlArray)){
            if(self::isAssoc($xmlArray[$key])){
                $toret[] =   $xmlArray[$key];
            }else{
                $toret = $xmlArray[$key];
            }
        }
        return $toret;
    }

    static function xml2Array ( $xml , $recursive = false )
    {
        if ( ! $recursive ){
            $array = simplexml_load_string ( $xml ) ;
        } else {
            $array = $xml ;
        }

        $newArray = array () ;
        $array = ( array ) $array ;
        foreach ( $array as $key => $value ){
            $value = ( array ) $value ;
            if(is_string($value)){
                $newArray [ strtolower ($key) ] = trim($value) ;
            }else if (!self::isAssoc($value ) && isset($value [0]) && sizeof($value)==1){
                $newArray [ strtolower ($key) ] = trim ( $value[0] ) ;
            } else {
                $newArray [ strtolower ($key) ] = self::xml2Array ( $value , true ) ;
            }
        }
        return $newArray ;
    }

    /**
     * @param mixed $a
     * @return bool true when $a is a non-empty associative array
     */
    private static function isAssoc($a)
    {
        if(!is_array($a) || $a === []) return false;
        return array_keys($a) !== range(0, count($a) - 1);
    }
}
