<?php
class FrameworkSpecificMapping{
    public function __construct($fi,$k,$v)
    {
        $this->FrameworkIdentifier=$fi;
        $this->Key=$k;
        $this->Value=$v;
    }

    var $FrameworkIdentifier;
    var $Key;
    var $Value;
}
