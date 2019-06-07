<?php

class FrameworkRange
{
    public function __construct($min, $max, $incMin, $incMax)
    {
        $this->Min = $min;
        $this->Max = $max;
        $this->IncludeMin = $incMin;
        $this->IncludeMax = $incMax;
    }


    var $Min;
    var $Max;
    var $IncludeMin;
    var $IncludeMax;

    public function Satisfies($framework)
    {
        return FrameworkRange::SameExceptForVersion($this->Min, $framework)
            && ($this->IncludeMin ? $this->Min->Version->Lte($framework->Version) : $this->Min->Version->Lt($framework->Version))
            && ($this->IncludeMax ? $this->Max->Version->Gte($framework->Version) : $this->Max->Version->Gt($framework->Version));
    }

    public static function Equals($x,$y){
        strcasecmp($x->Min->Framework,$y->Min->Framework)==0  &&
        NugetFramework::Equals($x->Min,$y->Min) && NugetFramework::Equals($x->Max,$y->Max) &&
        $x->IncludeMin==$y->IncludeMin && $x->IncludeMax==$y->IncludeMax;
    }

    private static function SameExceptForVersion($x, $y)
    {
        return strcasecmp($x->Framework, $y->Framework) == 0
            && (strcasecmp($x->Profile, $y->Profile) == 0);
    }
}