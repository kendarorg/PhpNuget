<?php

class ReportGroup{
    var $group;
    var $data=array();
    var $tags=array();
    var $isGroup=true;
    public function __construct($group,$isGroup=true)
    {
        $this->group = $group;
        $this->isGroup = $isGroup;
    }
}
