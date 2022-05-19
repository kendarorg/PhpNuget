<?php

namespace lib\db\parser;

class Operator
{
    public $Type;
    public $Value;
    public $Id;
    public $Children = array();
    public function toString()
    {
        $t = strtolower($this->Type);

        if($t=="string"){
            return "'".$this->Value."'";
        }
        $r = $this->Value;
        if($t=="function"){
            $r .="(";
        }
        for($i=0;$i<sizeof($this->Children);$i++){
            $c = $this->Children[$i];
            $r.=$c->toString();
            if(($i+1)<sizeof($this->Children) && $t=="function"){
                $r.=",";
            }

        }
        if($t=="function"){
            $r .=")";
        }
        return $r;
    }
}