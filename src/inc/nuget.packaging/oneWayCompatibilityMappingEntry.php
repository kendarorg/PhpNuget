<?php

class OneWayCompatibilityMappingEntry{
    var $TargetFrameworkRange;
    var $SupportedFrameworkRange;
    public function  __construct($t,$s)
    {
        $this->SupportedFrameworkRange=$s;
        $this->TargetFrameworkRange=$t;
    }

    public static function Equals($x,$y){
        if($x==null && $y==null)return  true;
        if($x==null && $y!=null) return  false;
        if($y==null && $x!=null) return  false;
        return  FrameworkRange::Equals($x->TargetFrameworkRange,$y->TargetFrameworkRange)  &&
            FrameworkRange::Equals($x->SupportedFrameworkRange,$y->SupportedFrameworkRange);

    }
}
