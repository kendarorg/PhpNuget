<?php

namespace lib\nuget\fields\file;

use lib\db\utils\SpecialFieldType;

class FileDependencyCompositeField extends SpecialFieldType
{
    public function isComposite(){
        $args = func_get_args();
        if(is_array($args[0])){
            $args = $args[0];
        }
        foreach ($args as $arg){
            $id = strtolower($arg->Id);
            if(is_array($arg->Value) &&  ("dependencies"==$id)) {
                return true;
            }
        }
        return false;
    }
}