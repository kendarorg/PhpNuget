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
		
        $zip = zip_open($this->zipFile);
        if ($zip) {
            while ($zip_entry = zip_read($zip)) {
                $zip_entry_name = zip_entry_name($zip_entry);
                if (!is_dir($zip_entry_name) && $path == $zip_entry_name) {
                    zip_entry_open($zip, $zip_entry, "r");
                    $entry_content = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                    zip_entry_close( $zip_entry);
                    zip_close($zip);
                    return $entry_content;
                }
            }
        }
        return null;
    }
    
    function GenerateInfos() {
        $zip = zip_open($this->zipFile);
        $folder_count   = 0;
        $file_count     = 0;
        $unzipped_size  = 0;
        $ext_array      = array ();
        $ext_count      = array ();
        //$entries_list      = array ();
        $entries_name      = array ();
        if ($zip) {
            while ($zip_entry = zip_read($zip)) {
                $zip_entry_name = zip_entry_name($zip_entry);
                
				
                if (is_dir($zip_entry_name)) {
                    $folder_count++;
                }else {
                    //$entries_list[]=$zip_entry;
                    $entries_name[]=$zip_entry_name;
                    $file_count++;
                }
                $path_parts = pathinfo(zip_entry_name($zip_entry));
                $ext = strtolower(trim(isset ($path_parts['extension']) ? $path_parts['extension'] : ''));
                if($ext != '') {
                    $ext_count[$ext]['count'] = isset ( $ext_count[$ext]['count']) ?  $ext_count[$ext]['count'] : 0;
                    $ext_count[$ext]['count']++;
                }
                $unzipped_size = $unzipped_size + zip_entry_filesize($zip_entry);
            }
        }
        $zipped_size = $this->get_file_size_unit(filesize($this->zipFile));
        $unzipped_size = $this->get_file_size_unit($unzipped_size);
        $zip_info = array ("folders"=>$folder_count,
                           "files"=>$file_count,
                           "zipped_size"=>$zipped_size,
                           "unzipped_size"=>$unzipped_size,
                           "file_types"=>$ext_count,
                           //"entries_list"=>$entries_list,
                           "entries_name"=>$entries_name
                        );
        zip_close($zip);
        return $zip_info ;
    }
    function get_file_size_unit($file_size){
        if($file_size/1024 < 1){
            return $file_size."Bytes";
        }else if($file_size/1024 >= 1 && $file_size/(1024*1024) < 1){
            return ($file_size/1024)."KB";
        }else{
            return $file_size/(1024*1024)."MB";
        }
    }

}
?>