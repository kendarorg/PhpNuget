<?php

namespace integration;

use lib\http\Request;

class MockRequest extends Request
{
    public $headers = array();
    public $responseCode = 200;
    public $readFile = "";
    public $content = "";
    public function header( $string)
    {
        $this->headers[]=$string;
    }

    public function http_response_code( $int)
    {
        $this->responseCode=$int;
    }

    public function readfile($path)
    {
        $this->readFile = $path;
    }

    public function show( $content)
    {
        $this->content = $content;
    }

    public function addExtraData(string $string, string $string1)
    {
        $this->extraData[strtolower($string)] = $string1;
    }

}