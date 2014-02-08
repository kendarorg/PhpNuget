<?php
if(!defined('__ROOT__')){
    define('__ROOT__', dirname(dirname(__FILE__)));
}
require_once(__ROOT__.'/settings.php'); 
/* From
 * http://stackoverflow.com/questions/189113/how-do-i-get-current-page-full-url-in-php-on-a-windows-iis-server
 */
class VirtualDirectory
{
    var $protocol;
    var $site;
    var $thisfile;
    var $real_directories;
    var $num_of_real_directories;
    var $virtual_directories = array();
    var $num_of_virtual_directories = array();
    var $baseurl;
    var $thisurl;
    public function __construct() 
    {
        $this->initialize();
    }
    
    public function VirtualDirectory()
    {
        $this->initialize();
    }
    function initialize()
    {
        $this->protocol = "http";
        if(isset($_SERVER['HTTPS'])){
            $this->protocol = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
        }
        $this->site = $this->protocol . '://' . $_SERVER['HTTP_HOST'];
        $this->thisfile = basename($_SERVER['SCRIPT_FILENAME']);
        $this->real_directories = $this->cleanUp(explode("/", str_replace($this->thisfile, "", $_SERVER['PHP_SELF'])));
        $this->num_of_real_directories = count($this->real_directories);
        $this->virtual_directories = array_diff($this->cleanUp(explode("/", str_replace($this->thisfile, "", $_SERVER['REQUEST_URI']))),$this->real_directories);
        $this->num_of_virtual_directories = count($this->virtual_directories);
        $this->baseurl = $this->site . "/" . implode("/", $this->real_directories) . "/";
if($this->baseurl==$this->site."//") $this->baseurl = $this->site."/";
        $this->thisurl = $this->baseurl . implode("/", $this->virtual_directories) . "/";
    }
    
    function upFromLevel($source,$upDirs)
    {
        $exploded = explode("/",$source);
        $sliced = array_slice($exploded,0,sizeof($exploded)-$upDirs-1);
        return implode("/",$sliced);
    }
    
    function cleanUp($array)
    {
        $cleaned_array = array();
        foreach($array as $key => $value)
        {
            $qpos = strpos($value, "?");
            if($qpos !== false)
            {
                break;
            }
            if($key != "" && $value != "")
            {
                $cleaned_array[] = $value;
            }
        }
        return $cleaned_array;
    }
}

?>