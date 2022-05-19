<?php

namespace lib\http;

use lib\OminousFactory;
use lib\utils\Properties;

class BaseHandler
{
    /**
     * @var Properties
     */
    protected $properties;
    /**
     * @var Request
     */
    private $request;

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

            $this->request = OminousFactory::getObject("request");
            $this->preHandle($this->request);
            if(!$this->catchAll($this->request)) {
                if ($this->request->getMethod() == "put") {
                    $this->put($this->request);
                } else if ($this->request->getMethod() == "post") {
                    $this->post($this->request);
                } else if ($this->request->getMethod() == "get") {
                    $this->httpGet($this->request);
                } else if ($this->request->getMethod() == "delete") {
                    $this->delete($this->request);
                } else if ($this->request->getMethod() == "option") {
                    $this->option($this->request);
                }
            }
            $this->postHandle($this->request);
        } catch (HandlerException $ex) {
            $this->request->header('Status: ' . $ex->getCode() . ' ' . $ex->getMessage());
            $this->request->http_response_code($ex->getCode());
            if ($ex->contentType != null) {
                $this->request->header("content-type: " . $ex->contentType);
            }
            if ($ex->content != null || $ex->getMessage()!=null) {
                $this->request->show( $ex->content ." ".$ex->getMessage());
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
        $this->request->http_response_code(200);
        if ($contentType != null) {
            $this->request->header("content-type: " . $contentType);
        }
        if ($content != null) {
            $this->request->show($content);
        }
    }

    /**
     * @param string $content
     * @param string $contentType
     * @return void
     */
    public function answerFile($path, $contentType)
    {
        $this->request->http_response_code(200);
        $this->request->header('Content-Type: '.$contentType);
        $this->request->header('Content-Disposition: attachment; filename='.basename($path));
        $this->request->header('Expires: 0');
        $this->request->header('Cache-Control: must-revalidate');
        $this->request->header('Pragma: public');
        $this->request->header('Content-Length: ' . filesize($path));
        $this->request->readfile($path);
    }

    /**
     * @param string $content
     * @param string $contentType
     * @return void
     */
    public function answerString($content, $contentType)
    {
        $this->request->http_response_code(200);
        $this->request->header('Content-Type: '.$contentType);
        $this->request->header('Expires: 0');
        $this->request->header('Cache-Control: must-revalidate');
        $this->request->header('Pragma: public');
        $this->request->show($content);
    }

    /**
     * @param mixed $object
     * @param string $prefix
     * @param string $postfix
     * @return void
     */
    public function answerJson($object, $prefix="",$postfix="")
    {
        $this->request->http_response_code(200);
        $this->request->header('Content-Type: application/json');
        $this->request->header('Expires: 0');
        $this->request->header('Cache-Control: must-revalidate');
        $this->request->header('Pragma: public');
        $json = $prefix.json_encode($object).$postfix;
        $this->request->show($json);
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function catchAll($request){
        return false;
    }
}