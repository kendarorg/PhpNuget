<?php

namespace lib\http;

use lib\utils\ArrayUtils;
use lib\utils\Guid;
use lib\utils\PathUtils;

class UploadedFile
{
    /**
     * @var string
     */
    public $mime;
    /**
     * @var string
     */
    public $tmpName;
    /**
     * @var integer
     */
    public $size;
    /**
     * @var string
     */
    public $name;
    /**
     * @var integer
     */
    public $errorCode;
    /**
     * @var string
     */
    public $errorMessage;
    /**
     * @var string
     */
    public $extension;

    /**
     * @param array $fileData
     */
    public function __construct($fileData)
    {
        $this->name = $fileData["name"];
        $this->mime = ArrayUtils::getIfExists($fileData,"type", "application/octet-stream");
        $this->tmpName = ArrayUtils::getIfExists($fileData,"tmp_name",Guid::newGuid());
        $this->size = ArrayUtils::getIfExists($fileData,"size",0);
        $this->errorCode = ArrayUtils::getIfExists($fileData,"error",0);

        if(!file_exists($this->tmpName) && !$this->hasError()) {
            $this->errorCode = 101;
        }
        $this->extension = PathUtils::getExtension($this->name);
        $this->errorMessage = $this->translateError($this->errorCode);
    }

    /**
     * @return bool
     */
    public function hasError(){
        return $this->errorCode!=0;
    }

    public function saveFile($destination){
        if($this->hasError()){
            return false;
        }
        if(file_exists($this->tmpName)) {
            if (!move_uploaded_file($this->tmpName, $destination)) {
                return false;
            }
        }
        return true;
    }
    /**
     * @param integer $errorCode
     * @return string
     */
    private function translateError($errorCode)
    {
        switch ($errorCode){
            case 0: return null;
            case 1: return "UPLOAD_ERR_INI_SIZE";
            case 2: return "UPLOAD_ERR_FORM_SIZE";
            case 3: return "UPLOAD_ERR_PARTIAL";
            case 4: return "UPLOAD_ERR_NO_FILE";
            case 6: return "UPLOAD_ERR_NO_TMP_DIR";
            case 7: return "UPLOAD_ERR_CANT_WRITE";
            case 8: return "UPLOAD_ERR_EXTENSION";
            case 101: return "LOCAL_MISSING_TMP_FILE";
            default: return "UNKNOWN_".$errorCode;
        }
    }
}