<?php

class Converter
{
    static function isArray(&$value)
    {
        if(self::isString($value))return false;
        return is_array($value);
    }
    static function toBoolean(&$value,$fieldName=null){

        if($value===null)return false;
        if($fieldName===null) {
            if (is_bool($value)) {
                return $value;
            } else if (is_string($value)) {
                return strtolower($value) === "true";
            } else if (is_numeric($value)) {
                return $value > 0;
            }
        }
        if(isset($value[$fieldName])){
            return self::toBoolean($value[$fieldName]);
        }
        return false;
    }

    static function normalizeBooleans(&$data, ...$fields)
    {
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = self::toBoolean($data[$field]);
            }
        }
    }

    static function isString(&$data, $fieldName=null){
        if($fieldName===null)return is_string($data);
        if(isset($data[$fieldName])){
            return self::isString($data[$fieldName]);
        }
        return false;
    }

    static function isEmptyString(&$data, $fieldName=null){
        if($fieldName!==null){
            if(isset($data[$fieldName])){
                return self::isEmptyString($data[$fieldName]);
            }
            return true;
        }
        if($data===null) return true;
        if(self::isString($data)){
            return strlen(Str::trim($data))===0;
        }
        return false;
    }

    static function isBoolean(&$value, $fieldName=null){
        if($fieldName===null) {
            if (is_bool($value)) {
                return $value;
            } else if (is_string($value)) {
                return strtolower($value) === "true";
            } else if (is_numeric($value)) {
                return $value > 0;
            }
        }
        if(isset($value[$fieldName])){
            return self::isBoolean($value[$fieldName]);
        }
        return false;
    }


    function toFloat($value,$useDotForDecimal = true)
    {
        if (is_null($value) || (is_string($value) && $value === '')) {
            return 0.0;
        }
        if(is_string($value)) {
            $value = trim($value);
            if(strcmp(strtoupper($value),'N.D.')==0) return null;

            $value = str_replace('€', '', $value);
            $value = str_replace(' ', '', $value);
            if ($useDotForDecimal) {
                $value = str_replace(',', '', $value); // Replace comma with dot for float conversion
            } else {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '', $value);// Replace dot with comma for float conversion
            }
        }else{
            return floatval(sprintf("%.2f",$value));
        }
        return floatval(sprintf("%.2f",floatval($value)));
    }


    function toInt($value,$useDotForDecimal = true)
    {
        if (is_null($value) || (is_string($value) && $value === '')) {
            return 0;
        }
        if(is_string($value)) {
            $value = trim($value);
            if(strcmp(strtoupper($value),'N.D.')==0) return null;

            $value = str_replace('€', '', $value);
            $value = str_replace(' ', '', $value);
            if ($useDotForDecimal) {
                $value = str_replace(',', '', $value); // Replace comma with dot for float conversion
            } else {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '', $value);// Replace dot with comma for float conversion
            }
        }
        return intval($value);
    }
}
