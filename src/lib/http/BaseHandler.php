<?php

namespace lib\http;

use lib\utils\Properties;

class BaseHandler
{
    /**
     * @var Properties
     */
    protected $properties;

    /**
     * @param Properties $properties
     */
    public function __construct($properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return void
     */
    public function handle()
    {
        try {
            $request = new Request();
            $this->preHandle($request);
            if ($request->getMethod() == "put") {
                $this->put($request);
            } else if ($request->getMethod() == "post") {
                $this->post($request);
            } else if ($request->getMethod() == "get") {
                $this->httpGet($request);
            } else if ($request->getMethod() == "delete") {
                $this->delete($request);
            } else if ($request->getMethod() == "option") {
                $this->option($request);
            }
            $this->postHandle($request);
        } catch (HandlerException $ex) {
            header('Status: ' . $ex->getCode() . ' ' . $ex->getMessage());
            http_response_code($ex->getCode());
            if ($ex->contentType != null) {
                header("content-type: " . $ex->contentType);
            }
            if ($ex->content != null) {
                echo $ex->content;
            }
        }
    }

    /**
     * @param Request $request
     * @return void
     * @throws HandlerException
     */
    protected function put($request)
    {
        throw new HandlerException("Method not allowed", 405);
    }

    /**
     * @param Request $request
     * @return void
     * @throws HandlerException
     */
    protected function post($request)
    {
        throw new HandlerException("Method not allowed", 405);
    }

    /**
     * @param Request $request
     * @return void
     * @throws HandlerException
     */
    protected function delete($request)
    {
        throw new HandlerException("Method not allowed", 405);
    }

    /**
     * @param Request $request
     * @return void
     * @throws HandlerException
     */
    protected function option($request)
    {
        throw new HandlerException("Method not allowed", 405);
    }

    /**
     * @param Request $request
     * @return void
     * @throws HandlerException
     */
    protected function httpGet($request)
    {
        throw new HandlerException("Method not allowed", 405);
    }

    /**
     * @param Request $request
     * @return void
     */
    protected function preHandle($request)
    {
    }

    /**
     * @param Request $request
     * @return void
     */
    protected function postHandle($request)
    {
    }

    /**
     * @param string $content
     * @param string $contentType
     * @return void
     */
    public function answerOk($content = null, $contentType = null)
    {
        http_response_code(200);
        if ($contentType != null) {
            header("content-type: " . $contentType);
        }
        if ($content != null) {
            echo $content;
        }
    }

    /**
     * @param string $content
     * @param string $contentType
     * @return void
     */
    public function answerFile($path, $contentType)
    {
        http_response_code(200);
        header('Content-Type: '.$contentType);
        header('Content-Disposition: attachment; filename='.basename($path));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }

    /**
     * @param mixed $object
     * @param string $prefix
     * @param string $postfix
     * @return void
     */
    public function answerJson($object, $prefix="",$postfix="")
    {
        http_response_code(200);
        header('Content-Type: application/json');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        $json = $prefix.json_encode($object).$postfix;
        echo $json;
    }
}