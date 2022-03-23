<?php

namespace lib;



use lib\db\file\FileDbStorage;
use lib\db\file\MySqlDbStorage;
use lib\db\QueryParser;
use lib\http\Request;
use lib\nuget\fields\mysql\NugetPackageConverter;
use lib\nuget\NugetDownloads;
use lib\nuget\NugetPackages;
use lib\nuget\NugetUsers;
use lib\rest\utils\LastQueryBuilder;
use lib\rest\utils\NugetQueryHandler;
use lib\rest\utils\ResourcesLoader;
use lib\utils\Properties;
use lib\rest\utils\NugetResultParser;

class OminousFactory
{
    private static $instance = null;
    private function __construct()
    {
        $this->intializeInt();
    }

    public $cache = array();
    public $generated = array();
    public static function getObject($name){
        $name = strtolower($name);
        if(self::$instance==null){
            self::$instance = new OminousFactory();
        }
        return self::$instance->getObjectInt($name);


    }

    private function getObjectInt($name)
    {
        $name =strtolower($name);
        if(isset($this->generated[$name])){
            return $this->generated[$name];
        }
        if(!isset($this->cache[$name])){
            throw new \Exception("MISSING ".$name." COMPONENT/VALUE");
        }
        $this->generated[$name] = $this->cache[$name]();
        return $this->generated[$name];
    }

    public static function setObject( $name, $value)
    {
        $name = strtolower($name);
        if(self::$instance==null){
            self::$instance = new OminousFactory();
        }
        self::$instance->setObjectInt($name,$value);
    }

    private function setObjectInt( $name,  $value)
    {
        $name =strtolower($name);
        $this->generated[$name] = $value;
    }

    public static function initialize( )
    {
        if(self::$instance==null){
            self::$instance = new OminousFactory();
        }
        self::$instance-> intializeInt( );
    }


    private function addCache($name,$func){
        $name =strtolower($name);
        $this->cache[$name] = $func;
    }
    /**
     * @return void
     */
    private function intializeInt(): void
    {
        $this->cache=[];
        $this->generated=[];
        $this->addCache("mysqli", function () {
            throw new \Exception("MISSINGSQLI");
        });
        $this->addCache("nugetdownloads", function () {
            return new NugetDownloads();
        });
        $this->addCache("request",function () {
            return new Request();
        });
        $this->addCache("properties", function () {
            return new Properties();
        });
        $this->addCache("nugetusers",function () {
            return new NugetUsers(self::getObject("nugetusersstorage"));
        });
        $this->addCache("nugetusersstorage",function () {
            $properties = self::getObject("properties");
            $dbType = $properties->getProperty("dbtype", "file");
            if ($dbType == "mysql") {
                return new MySqlDbStorage($properties, new QueryParser(), null, self::getObject("mysqli"));
            } else {
                return new FileDbStorage($properties, new QueryParser());
            }
        });
        $this->addCache("nugetpackages", function () {
            return new NugetPackages(self::getObject("nugetpackagesstorage"), self::getObject("properties"));
        });
        $this->addCache("nugetpackagesstorage", function () {
            $properties = self::getObject("properties");
            $dbType = $properties->getProperty("dbtype", "file");
            if ($dbType == "mysql") {
                return new MySqlDbStorage($properties, new QueryParser(), null, self::getObject("mysqli"), new NugetPackageConverter());
            } else {
                return new FileDbStorage($properties, new QueryParser());
            }
        });
        $this->addCache("resourcesloader", function () {
            return new ResourcesLoader(self::getObject('resourcesLoaderVersion'));
        });
        $this->addCache("nugetqueryhandler", function () {
            return new NugetQueryHandler(self::getObject("nugetpackages"));
        });
        $this->addCache("lastquerybuilder", function () {
            return new LastQueryBuilder();
        });
        $this->addCache("nugetresultparser", function () {
            return new NugetResultParser(self::getObject("resourcesloader"), self::getObject("lastquerybuilder"));
        });
    }
}