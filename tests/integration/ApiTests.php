<?php

namespace integration;

use lib\http\Request;
use lib\OminousFactory;
use PHPUnit\Framework\TestCase;

class ApiTests extends TestCase
{
    public function testOne(){
        $_SERVER['REQUEST_METHOD'] = "POST";
        $request = new Request();
        $this->assertEquals("post",$request->getMethod());
    }
}