<?php

namespace lib\rest\utils;

class Pagination
{
    public $Skip = 0;
    public $Top = 10;

    /**
     * @param Request $request
     * @return Pagination
     */
    public function buildFromRequest($request){
        $this->Skip = $request->getInteger("\$skip",0);
        $this->Top = $request->getInteger("\$top",1000);
        return $this;
    }
}