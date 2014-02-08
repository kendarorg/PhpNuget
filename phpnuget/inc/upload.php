<?php
if(!defined('__ROOT__')) define('__ROOT__',dirname( dirname(__FILE__)));
require_once(__ROOT__."/inc/utils.php");

class Uploader
{
    var $destinationDir;
    
    public function __construct($destinationDir) 
    {
        $this->initialize($destinationDir);
    }
    
    public function Uploader($destinationDir)
    {
        $this->initialize($destinationDir);
    }
    function initialize($destinationDir)
    {
        $this->destinationDir = $destinationDir;
    }
    
    function Upload($allowedExts,$maxSize) { 
        $guid = getGUID();
        $toret = array(); 
        $toret["hasError"] = false; $toret["errorCode"] = null; 
        $toret["errorMessage"] = ""; $toret["name"]=$_FILES["file"]["name"]; 
        $toret["mime"] = $_FILES["file"]["type"]; 
        $toret["sizeBytes"] = $_FILES["file"]["size"]; 
        $exploded  = explode(".", $toret["name"]);
        $extension = end($exploded);
        
        if ( $toret["sizeBytes"] >= $maxSize){
            $toret["hasError"] = true;
            $toret["errorMessage"] = "Max size is '".$maxSize."' bytes. File size is '".$toret["sizeBytes"]."'.";
        } else if ( !in_array($extension, $allowedExts)){
            $toret["hasError"] = true;
            $toret["errorMessage"] = "Extension '".$extension."' not allowed. ".
                "The allowed ones are '".implode(", ",$allowedExts)."'";
        }else {
          if ($_FILES["file"]["error"] > 0){
            $toret["hasError"] = true;
            $toret["errorCode"]= $_FILES["file"]["error"];
          }else{
            $toret["tmpName"] = $_FILES["file"]["tmp_name"];
            
            if (file_exists($this->destinationDir."/" . $guid)){
                unlink ($this->destinationDir."/" . $guid);
            }
            $toret["destination"]=$this->destinationDir."/" . $guid;
            move_uploaded_file($toret["tmpName"],$toret["destination"]);
          }
        }
        return $toret;
    }
}
?>