<?php
require_once(__DIR__."/vendor/autoload.php");


use lib\utils\Properties;

$settings = __DIR__."/conf/properties.json";
$defaultSettings = __DIR__."/conf/properties.json";

if(file_exists($settings)) {
    Properties::initialize($settings);
}else{
    Properties::initialize($defaultSettings);
}
?>