<?php

namespace lib\http;


class HandlerException extends \Exception
{
    /**
     * @var string
     */
    public $content = null;
    /**
     * @var string
     */
    public $contentType = null;

    /**
     * @param string $message
     * @param integer $code
     * @param \Throwable $previous
     */
    public function __construct($message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}