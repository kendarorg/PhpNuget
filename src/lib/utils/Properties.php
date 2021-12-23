<?php

namespace lib\utils;

class Properties
{
    private static $staticProperty = null;
    /**
     * @var array
     */
    private $properties = array();

    /**
     * @param string $fileName
     */
    public function __construct($fileName)
    {
        if(self::$staticProperty!=null){
            return;
        }
        if($fileName!=null) {
            $content = file_get_contents($fileName);
            $properties = json_decode($content, true);
            ArrayUtils::mergeLowerCase($this->properties, $properties);
        }
    }

    public static function initialize(string $path)
    {
        self::$staticProperty = new Properties($path);
        return self::$staticProperty;
    }

    /**
     * @param string $key
     * @param string null $default
     * @return string|null
     */
    public function getProperty($key,$default = null){
        $th = $this;
        if(self::$staticProperty!=null){
            $th=self::$staticProperty;
        }
        $key = strtolower($key);
        if(isset($th->properties[$key])){
            return $th->properties[$key];
        }
        return $default;
    }

    /**
     * @param string $key
     * @param string|null $value
     * @return void
     */
    public function setProperty($key,$value){
        $th = $this;
        if(self::$staticProperty!=null){
            $th=self::$staticProperty;
        }
        $key = strtolower($key);
        $th->properties[$key] = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasProperty($key)
    {
        $th = $this;
        if(self::$staticProperty!=null){
            $th=self::$staticProperty;
        }
        $key = strtolower($key);
        return isset($th->properties[$key]);
    }
}