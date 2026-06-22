<?php


//http://www.innovativephp.com/analyze-zip-file-contents-using-php/
//http://php.net/manual/en/function.zip-open.php

class ZipManager
{
    var $zipFile;
    public function __construct($zipFile)
    {
        $this->initialize($zipFile);
    }

    private function initialize($zipFile)
    {
        $this->zipFile = $zipFile;
        if(!file_exists($this->zipFile)){
            throw new Exception("Missing file ".$this->zipFile);
        }
    }

    public function LoadFile($path)
    {
        $zip = new \ZipArchive();
        if ($zip->open($this->zipFile) !== true) {
            return null;
        }
        $content = $zip->getFromName($path);
        $zip->close();
        return $content === false ? null : $content;
    }

    function GenerateInfos() {
        $zip = new \ZipArchive();
        $folder_count   = 0;
        $file_count     = 0;
        $unzipped_size  = 0;
        $ext_count      = array ();
        $entries_name   = array ();
        if ($zip->open($this->zipFile) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $zip_entry_name = $stat['name'];

                if (substr($zip_entry_name, -1) === '/') {
                    $folder_count++;
                } else {
                    $entries_name[] = $zip_entry_name;
                    $file_count++;
                }
                $path_parts = pathinfo($zip_entry_name);
                $ext = strtolower(trim(isset ($path_parts['extension']) ? $path_parts['extension'] : ''));
                if($ext != '') {
                    $ext_count[$ext]['count'] = isset ( $ext_count[$ext]['count']) ?  $ext_count[$ext]['count'] : 0;
                    $ext_count[$ext]['count']++;
                }
                $unzipped_size = $unzipped_size + $stat['size'];
            }
            $zip->close();
        }
        $zipped_size = $this->getFileSizeUnit(filesize($this->zipFile));
        $unzipped_size = $this->getFileSizeUnit($unzipped_size);
        $zip_info = array ("folders"=>$folder_count,
            "files"=>$file_count,
            "zipped_size"=>$zipped_size,
            "unzipped_size"=>$unzipped_size,
            "file_types"=>$ext_count,
            //"entries_list"=>$entries_list,
            "entries_name"=>$entries_name
        );
        return $zip_info ;
    }
    private function getFileSizeUnit($file_size){
        if($file_size/1024 < 1){
            return $file_size."Bytes";
        }else if($file_size/1024 >= 1 && $file_size/(1024*1024) < 1){
            return ($file_size/1024)."KB";
        }else{
            return $file_size/(1024*1024)."MB";
        }
    }

}