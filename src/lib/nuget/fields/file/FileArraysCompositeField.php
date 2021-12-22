<?php

namespace lib\nuget\fields\file;

use lib\db\parser\InternalTypeBuilder;
use lib\db\utils;
use function contains;

class FileArraysCompositeField extends utils\SpecialFieldType
{
    public function isComposite(){
        $args = func_get_args();
        if(is_array($args[0])){
            $args = $args[0];
        }
        foreach ($args as $arg){
            $id = strtolower($arg->Id);
            if(is_array($arg->Value) &&  ("author"==$id || "owners"==$id
                    || "references"==$id)) {
                return true;
            }
        }
        return false;
    }

    public function doeq($args){
        return $this->buildRealVals($args,function($l,$r){ return $l==$r;});
    }

    public function doneq($args){
        return $this->buildRealVals($args,function($l,$r){ return $l!=$r;});
    }
    function dolt($args){
        return $this->buildRealVals($args,function($l,$r){
            return strcasecmp ($l,$r)<0;});
    }
    function dogt($args){
        return $this->buildRealVals($args,function($l,$r){
            return strcasecmp ($l,$r)>0;});
    }
    function dolte($args){
        return $this->buildRealVals($args,function($l,$r){
            return strcasecmp ($l,$r)<0 || $l==$r;});
    }
    function dogte($args){
        return $this->buildRealVals($args,function($l,$r){
            return strcasecmp ($l,$r)>0|| $l==$r;});
    }
    public function substringof($args)
    {
        return $this->buildRealVals($args,function($l,$r){
            return contains ($l,$r);});
    }
    function startswith($args)
    {
        return $this->buildRealVals($args,function($l,$r){
            return $this->startsWithInt($l,$r);});
    }
    function endswith($args)
    {
        return $this->buildRealVals($args,function($l,$r){
            return $this->endsWithInt($l,$r);});
    }
    function startsWithInt($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }
    function endsWithInt($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }
}