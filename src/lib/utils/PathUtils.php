<?php

namespace lib\utils;

class PathUtils
{
    /**
     * @param string $path
     * @return string
     */
    public static function getExtension($path){
        $exploded  = explode(".", $path);
        return end($exploded);
    }

    /**
     * @return string
     */
    public static function combine(){
        $args = func_get_args();
        $realArgs = [];
        foreach ($args as $arg){
            $arg = trim($arg,"\\/");
            if(strlen($arg)>0) {
                $realArgs[] = $arg;
            }
        }
        return join(DIRECTORY_SEPARATOR,$realArgs);
    }

    /**
     * @param $data
     * @return string
     */
    public static function writeTemporaryFile($data = null)
    {
        $name = tempnam(sys_get_temp_dir(), '');
        if($name==false){
            return null;
        }
        if($data !=null){
            file_put_contents($name,$data);
        }
        return $name;
    }
}