<?php

namespace lib\http;

use lib\utils\ArrayUtils;

class Request
{
    /**
     * @var array
     */
    public $files;
    /**
     * @var string[]
     */
    private $extraData;
    /**
     * @var string[]
     */
    private $requestData;

    /**
     * @var Authorization
     */
    public $authorization;
    /**
     * @var string
     */
    private $data;

    public function __construct()
    {
        $this->files = [];
        $this->extraData = [];
        $this->requestData = [];
        ArrayUtils::mergeLowerCase($this->files,$_FILES);
        ArrayUtils::mergeLowerCase($this->requestData,$_SERVER);
        ArrayUtils::mergeLowerCase($this->requestData,$_GET);
        ArrayUtils::mergeLowerCase($this->requestData,$_POST);
        ArrayUtils::mergeLowerCase($this->requestData,$_REQUEST);
        ArrayUtils::mergeLowerCase($this->requestData,$_COOKIE);
    }

    /**
     * @param string|string[] $value
     * @param string $default
     * @return string|null
     */
    public function getParam($value, $default = null)
    {
        if(is_array($value)){
            return $this->getParamArray($value,$default);
        }else{
            return $this->getParamString($value,$default);
        }
    }

    /**
     * @param string $value
     * @param bool $default
     * @return bool
     */
    public function getBoolean($value, $default =false){
        $res = strtolower($this->getParam($value,$default?"true":"false"));
        return $res=="true";
    }

    /**
     * @param string $value
     * @param integer $default
     * @return integer
     */
    public function getInteger($value, $default =-i){
        $res = strtolower($this->getParam($value,$default.""));
        return intval($res);
    }

    /**
     * @param string[] $values
     * @param string|null $default
     * @return string|null
     */
    public function getParamArray($values, $default = null)
    {
        foreach ($values as $value) {
            $partial = $this->getParamString($value);
            if ($partial != null) {
                return $partial;
            }
        }
        return $default;
    }

    /**
     * @param string $value
     * @param string|null $default
     * @return string|null
     */
    public function getParamString($value, $default = null)
    {
        $value = strtolower($value);
        if (isset($this->extraData[$value])) return $this->extraData[$value];
        if (isset($this->requestData[$value])) return $this->requestData[$value];
        return $default;
    }

    /**
     * @param string $value
     * @return bool
     */
    public function hasParam($value)
    {
        $value = strtolower($value);
        return isset($this->requestData[$value]) ||
            isset($this->extraData[$value]);
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        $protocol = "http";
        if (
            //straight
            isset($_SERVER['HTTPS']) && in_array($_SERVER['HTTPS'], ['on', 1])
            ||
            //proxy forwarding
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
        ) {
            $protocol = 'https';
        }

        return $protocol;
    }

    /**
     * @return bool
     */
    public function isUpload()
    {
        return sizeof($_FILES) > 0;
    }

    /**
     * @return void
     */
    public function initializeAsMultipart()
    {
        $data = array();
        new RawInputStream($data);
        ArrayUtils::mergeLowerCase($this->files,$data['file']);
        ArrayUtils::mergeLowerCase( $this->extraData,$data['post']);
    }

    /**
     * @return string
     */
    public function getJson($objectInstance){
        if($this->data == null){
            $data = file_get_contents("php://input");
            if($data==false){
                $data = "{}";
            }
        }
        if($this->data==false){
            $this->data = "{}";
        }
        $mapper = new JsonMapper();
        return $mapper->map(
            json_decode($this->data),
            $objectInstance
        );
    }
}