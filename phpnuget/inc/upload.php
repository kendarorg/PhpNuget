<?php
define('__ROOT__',dirname( dirname(__FILE__)));

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
    
    function Upload($allowedExts,$maxSize)
    {
        $toret = array();
        $toret["hasError"] = false;
        $toret["errorCode"] = null;
        $toret["errorMessage"] = "";
        $toret["name"]=$_FILES["file"]["name"];
        $toret["mime"] = $_FILES["file"]["type"];
        $toret["sizeBytes"] = $_FILES["file"]["size"];
        $extension = end(explode(".", $toret["name"]));
        
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
        
            if (file_exists($this->destinationDir."/" . $toret["name"])){
                $toret["hasError"] = true;
                $toret["errorMessage"] =  "'".$toret["name"] . "' already exists. ";
            } else {
                $toret["destination"]=$this->destinationDir."/" . $toret["name"];
              move_uploaded_file($toret["tmpName"],$toret["destination"]);
            }
          }
        }
        return $toret;
    }
}
?>