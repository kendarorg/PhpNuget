<?php

namespace lib\db;

use lib\db\parser\Operator;

class InternalTypeBuilder
{
    public static function buildBool($value)
    {
        $o = new Operator();
        $o->Type = "boolean";
        $o->Value = false;
        if($value==true || $value>=1 || $value=="true"){
            $o->Value = true;
        }
        return $o;
    }

    public static function buildItem($value,$type,$id)
    {
        $o = new Operator();
        $o->Type = strtolower($type);
        $o->Value = $value;
        $o->Id = $id;
        return $o;
    }
}