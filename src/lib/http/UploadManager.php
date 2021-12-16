<?php

namespace lib\http;

class UploadManager
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var UploadedFile[]
     */
    private $files;

    /**
     * @param Request $request
     */
    public function __construct($request)
    {
        $this->files = [];
        $this->request = $request;
        foreach($request->files as $fileId => $fileData){
            $this->files[$fileId] = new UploadedFile($fileData);
        }
    }

    /**
     * @return string[]
     */
    public function listFiles(){
        $result = [];
        foreach($this->files as $fileId => $fileData){
            $result[] = $fileId;
        }

        return $result;
    }

    /**
     * @param string $fileId
     * @return UploadedFile|null
     */
    public function getFile($fileId){
        if(isset($this->files[$fileId])){
            return $this->files[$fileId];
        }
        return null;
    }

    /**
     * @param string $fileId
     * @return bool
     */
    public function hasFile($fileId){
        return isset($this->files[$fileId]);
    }
}