<?php

class Version
{
    public function __construct($maj, $min, $build, $rev)
    {
        $this->Major = $maj;
        $this->Minor = $min;
        $this->Build = $build;
        $this->Revision = $rev;
    }

    var $Revision;
    var $Minor;
    var $Major;
    var $Build;

    public function Gt($other)
    {
        return $this->CompareTo($other) > 0;
    }

    public function Gte($other)
    {
        return $this->CompareTo($other) == 0 || $this->CompareTo($other) > 0;
    }

    public function Lt($other)
    {
        return $this->CompareTo($other) < 0;
    }

    public function Lte($other)
    {
        return $this->CompareTo($other) == 0 || $this->CompareTo($other) < 0;
    }

    public function Eq($other)
    {
        return $this->CompareTo($other) == 0;
    }

    public function CompareTo($value)
    {
        return
            $value == null ? 1 :
                $this->Major != $value->Major ? ($this->Major > $value->Major ? 1 : -1) :
                    $this->Minor != $value->Minor ? ($this->Minor > $value->Minor ? 1 : -1) :
                        $this->Build != $value->Build ? ($this->Build > $value->Build ? 1 : -1) :
                            $this->Revision != $value->Revision ? ($this->Revision > $value->Revision ? 1 : -1) :
                                0;
    }
}