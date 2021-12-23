<?php
require_once(dirname(__FILE__)."/utils.php");
require_once(dirname(__FILE__)."/url.php");
require_once(dirname(__FILE__)."/http.php");

class UploadUtils
{
    var $destinationDir;
	var $allowAll;
	var $maxSize;
	var $allowedExts;
    
    public function __construct($destinationDir,$allowedExts,$maxSize,$allowAll=false) 
    {
        $this->initialize($destinationDir,$allowedExts,$maxSize,$allowAll=false);      	
    }
	
    function initialize($destinationDir,$allowedExts,$maxSize,$allowAll=false)
    {
        $this->destinationDir = $destinationDir;
		$this->allowedExts = $allowedExts;
		$this->maxSize = $maxSize;
		$this->allowAll = $allowAll;
    }
	
	public static function IsUploadRequest()
	{
		if("post"!=UrlUtils::RequestMethod()) return false;
		return sizeof($_FILES)>0;
	}
    
    function Upload($fileId = "file") { 
		$files = NULL;
	
		
		$files = $_FILES;
		$isRealFile = true;
		if(!array_key_exists($fileId,$files)){
			$files = HttpUtils::RawRequest();
		}
		
		foreach($files as $fileId => $file){
			
			$guid = Utils::NewGuid();
			$toret = array(); 
			$toret["hasError"] = false; 
			$toret["errorCode"] = null; 
			$toret["errorMessage"] = ""; 
			$toret["name"]=$file["name"];
            $toret["sizeBytes"]=0;
			if(array_key_exists("mime",$file))$toret["mime"] = $file["type"]; 
			if(array_key_exists("tmp_name",$file))$toret["tmpName"] = $file["tmp_name"]; 
			if(array_key_exists("size",$file))$toret["sizeBytes"] = $file["size"]; 
			$exploded  = explode(".", $toret["name"]);
			$extension = end($exploded);
			
			if ( $toret["sizeBytes"] >= $this->maxSize){
				$toret["hasError"] = true;
				$toret["errorMessage"] = "Max size is '".$this->maxSize."' bytes. File size is '".$toret["sizeBytes"]."'.";
			}else if ( $this->allowAll<=0 && !in_array($extension, $this->allowedExts)){
				$toret["hasError"] = true;
				$toret["errorMessage"] = "Extension '".$extension."' not allowed. ".
					"The allowed ones are '".implode(", ",$this->allowedExts)."'";
			}else {
			  if (array_key_exists("error",$file) && $file["error"] > 0){
				//TODO Error translations http://php.net/manual/en/features.file-upload.errors.php
				$toret["hasError"] = true;
				$toret["errorCode"]= $file["error"];
			  }else{
				
				
				if (file_exists($this->destinationDir."/" . $guid)){
					unlink ($this->destinationDir."/" . $guid);
				}
				$toret["destination"]=$this->destinationDir."/" . $guid;
				
				if(file_exists($toret["tmpName"])){
					if(!move_uploaded_file($toret["tmpName"],$toret["destination"])){
						$toret["hasError"] = true;
						$toret["errorMessage"] =  'Cannot move file from ' . $toret["tmpName"] . ' to ' . $toret["destination"];
						$toret["errorCode"] = UPLOAD_ERR_CANT_WRITE;
					}
				}else{
					//if($toret["tmpName"]==null){
						file_put_contents($toret["destination"],$file["content"]);
					/*}
					else if(!rename($toret["tmpName"],$toret["destination"])){
						$toret["hasError"] = true;
						$toret["errorMessage"] = 'Cannot rename file from ' . $toret["tmpName"] . ' to ' . $toret["destination"];
						$toret["errorCode"] = UPLOAD_ERR_CANT_WRITE;
					}*/
				}
			  }
			}
			//uplogv("uploadutils","Files",$toret);
			//die();
			if($toret["hasError"]){
				unlink($toret["tmpName"]);
			}
	        return $toret;
	    }
    }
}
/*

UPLOAD_ERR_OK

    Value: 0; There is no error, the file uploaded with success.
UPLOAD_ERR_INI_SIZE

    Value: 1; The uploaded file exceeds the upload_max_filesize directive in php.ini.
UPLOAD_ERR_FORM_SIZE

    Value: 2; The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.
UPLOAD_ERR_PARTIAL

    Value: 3; The uploaded file was only partially uploaded.
UPLOAD_ERR_NO_FILE

    Value: 4; No file was uploaded.
UPLOAD_ERR_NO_TMP_DIR

    Value: 6; Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.
UPLOAD_ERR_CANT_WRITE

    Value: 7; Failed to write file to disk. Introduced in PHP 5.1.0.
UPLOAD_ERR_EXTENSION

    Value: 8; A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help. Introduced in PHP 5.2.0.

	*/
?>