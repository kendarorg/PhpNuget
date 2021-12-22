<?php

namespace lib\db\utils;

class SpecialFieldType
{
    public function isExternal()
    {
        return false;
    }
    public function isComposite(){
        return false;
    }
    public function buildToken($token)
    {
        return null;
    }
    public function canHandle($name,$params)
    {
        return false;
    }
    public function dolt($args)
    {
        return false;
    }
    public function dogte($args)
    {
        return false;
    }
    public function dogt($args)
    {
        return false;
    }
    public function dolte($args)
    {
        return false;
    }
    public function substringof($args)
    {
        return false;
    }

    public function dosort($name,$asc){
        return null;
    }
}