<?php

GlobalRegistry::$log = LogManager::getLogger("GlobalRegistry");

class GlobalRegistry{
    static $log;
    static $globalRegistry=[];
    static $globalRegistryValues=[];
    static $globalClasses=[];
    static function preLoadClasses(...$dirs){
        $classesToLoad = [];
        foreach ($dirs as $dir) {
            $lowerDir = strtolower($dir);
            if (!is_dir($dir)) {
                if(isset(self::$globalRegistry[$lowerDir])){
                    $dir = self::$globalRegistry[$lowerDir];
                }
                if(!is_dir($dir)){
                    continue; // skip invalid directories
                }
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                /** @var SplFileInfo $file */
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $basename = $file->getBasename('.php'); // filename without extension

                    if (preg_match('/^[A-Z]/', $basename)) {
                        if(str_ends_with($basename, '_singleton')){
                            $className = str_replace("_singleton", "", $basename);
                            $fileRealPath = $file->getRealPath();
                            $load = function() use ($fileRealPath) {
                                require_once $fileRealPath;
                            };
                            $load = $load->bindTo(null, null);
                            $load();
                            $classesToLoad[$className]=$className;
                            //self::register(new $className());
                        }elseif(str_ends_with($basename, '_base')){
                            $className = str_replace("_base", "", $basename);
                            $fileRealPath = $file->getRealPath();
                            $load = function() use ($fileRealPath) {
                                require_once $fileRealPath;
                            };
                            self::$log->debug("Loaded base $className");
                            $load = $load->bindTo(null, null);
                            $load();
                        }elseif(str_ends_with($basename, '_load')){
                            $className = str_replace("_load", "", $basename);
                            $fileRealPath = $file->getRealPath();
                            $load = function() use ($fileRealPath) {
                                require_once $fileRealPath;
                            };
                            self::$log->debug("Loaded util $className");
                            $load = $load->bindTo(null, null);
                            $load();
                        }else {
                            self::$globalClasses[strtolower($basename)] = $file->getRealPath();
                        }
                    }
                }
            }
        }
        while(count($classesToLoad)!=0){
            $toRemove = [];
            foreach ($classesToLoad as $className => $className2) {
                try{
                    $classNameLower = strtolower($className);
                    if(!isset(self::$globalRegistry[$classNameLower])){
                        self::$log->debug("Loading class $className singleton");
                        $cl = new $className();
                        self::register($cl);
                    }
                    $toRemove[]=$className;
                }catch (Exception $e){

                    self::$log->error("Error loading $className singleton",$e);
                }
            }
            foreach($toRemove as $class){
                unset($classesToLoad[$class]);
            }
        }
    }

    static function assertExists(...$values){
        $undefined = [];
        foreach($values as $value){
            $valueLower = strtolower($value);
            if(!isset(self::$globalRegistry[$valueLower])){
                $undefined[] = $value;
            }
        }
        if(count($undefined)>0){
            echo "Missing ".join(', ', $undefined);
            die();
        }
    }


    static function renderValues(){
        unset(self::$globalRegistryValues['DB_HOST']);
        unset(self::$globalRegistryValues['DB_NAME']);
        unset(self::$globalRegistryValues['DB_USER']);
        unset(self::$globalRegistryValues['DB_PASS']);
        unset(self::$globalRegistryValues['DB_CHARSET']);
        unset(self::$globalRegistryValues['NOREPLY_MAIL']);
        unset(self::$globalRegistryValues['NOREPLY_USER']);
        unset(self::$globalRegistryValues['NOREPLY_PASSWORD']);
        unset(self::$globalRegistryValues['NOREPLY_SMTP']);
        unset(self::$globalRegistryValues['NOREPLY_SMTP_PORT']);
        echo json_encode(self::$globalRegistryValues);
    }
    static function registerValue($name,$value){
        $nameLower = strtolower($name);
        $nameUpper = strtoupper($name);
        define($nameUpper,$value);
        self::$globalRegistryValues[$nameLower]=$value;
        self::$globalRegistry[$nameLower]=$value;
    }
    static function register($object,$byName=null){
        $classname = get_class($object);
        if($byName==null){$byName=$classname;}
        $byName = strtolower($byName);
        if(!isset(self::$globalRegistry[$byName])){
            self::$globalRegistry[$byName]=$object;
        }else{
            echo $classname." with name ".$byName." is already registered";
            throw new Exception($classname." with name ".$byName." is already registered");
        }
    }
    static function getByName($name){
        $name = strtolower($name);
        if(isset(self::$globalRegistry[$name])){
            return self::$globalRegistry[$name];
        }
        echo $name." Named instance".$name." had never been registered";
        die();
    }
    static function get($classname,...$args){
        $classnameLower = strtolower($classname);
        if(isset(self::$globalRegistry[$classnameLower])){
            return self::$globalRegistry[$classnameLower];
        }
        try{
            if(isset(self::$globalClasses[$classnameLower])){
                $fileRealPath = self::$globalClasses[$classnameLower];
                $load = function() use ($fileRealPath) {
                    require_once $fileRealPath;
                };

                self::$log->debug("Loaded lazy with args $classname");
                $load = $load->bindTo(null, null);
                $load();
                $target = new $classname(...$args);
                self::register($target);
            }else {
                self::$log->debug("Loading last resort with args $classname");
                $target = new $classname(...$args);
                self::register($target);
            }
            return $target;
        }catch (Exception $e){
            echo $classname." cannot be auto-registered";
            die();
        }
    }

    static function getNoThrow($classname,...$args){
        $classnameLower = strtolower($classname);
        if(isset(self::$globalRegistry[$classnameLower])){
            return self::$globalRegistry[$classnameLower];
        }
        try{
            if(isset(self::$globalClasses[$classnameLower])){
                $fileRealPath = self::$globalClasses[$classnameLower];
                $load = function() use ($fileRealPath) {
                    require_once $fileRealPath;
                };

                self::$log->debug("Loaded lazy with args $classname");
                $load = $load->bindTo(null, null);
                $load();
                if(!class_exists($classname)){
                    return null;
                }
                $target = new $classname(...$args);
                self::register($target);
            }else {
                self::$log->debug("Loading last resort with args $classname");
                if(!class_exists($classname)){
                    return null;
                }
                $target = new $classname(...$args);
                self::register($target);
            }
            return $target;
        }catch (Exception $e){
            return null;
        }
    }

    static function getTransient($classname,...$args){
        try{
            $classnameLower = strtolower($classname);
            self::$log->debug("Loading transient with args $classname");

            if(isset(self::$globalClasses[$classnameLower])){
                $fileRealPath = self::$globalClasses[$classnameLower];
                $load = function() use ($fileRealPath) {
                    require_once $fileRealPath;
                };
                $load = $load->bindTo(null, null);
                $load();
                return new $classname(...$args);
            }else {
                return new $classname(...$args);
            }
            throw new Exception("Class $classname does not exist");
        }catch (Exception $e){
            echo $classname." cannot be auto-registered";
            die();
        }
    }
}