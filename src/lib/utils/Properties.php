<?php

namespace lib\utils;

class Properties
{
    /**
     * @var array
     */
    private $properties = array();

    /**
     * @param string $fileName
     */
    public function __construct($fileName)
    {
        if($fileName!=null) {
            $content = file_get_contents($fileName);
            $properties = json_decode($content, true);
            ArrayUtils::mergeLowerCase($this->properties, $properties);
        }
    }

    /**
     * @param string $key
     * @param string null $default
     * @return string|null
     */
    public function getProperty($key,$default = null){
        $key = strtolower($key);
        if(isset($this->properties[$key])){
            return $this->properties[$key];
        }
        return $default;
    }

    /**
     * @param string $key
     * @param string|null $value
     * @return void
     */
    public function setProperty($key,$value){
        $key = strtolower($key);
        $this->properties[$key] = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasProperty($key)
    {
        $key = strtolower($key);
        return isset($this->properties[$key]);
    }
}