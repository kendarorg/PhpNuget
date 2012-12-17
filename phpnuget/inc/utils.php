<?php
function XML2Array ( $xml , $recursive = false )
{
    if ( ! $recursive )
    {
        $array = simplexml_load_string ( $xml ) ;
    }
    else
    {
        $array = $xml ;
    }
   
    $newArray = array () ;
    $array = ( array ) $array ;
    foreach ( $array as $key => $value )
    {
        
        $value = ( array ) $value ;
        if ( isset ( $value [ 0 ] ) )
        {
            $newArray [ strtolower ($key) ] = trim ( $value [ 0 ] ) ;
        }
        else
        {
            $newArray [ strtolower ($key) ] = XML2Array ( $value , true ) ;
        }
    }
    return $newArray ;
}

function DeleteDir($path)
{
    $files = glob($path.'/*.*'); // get all file names
    foreach($files as $file){ // iterate files
      if(is_file($file))
        unlink($file); // delete file
    }
    rmdir($path);
}

function startsWith($haystack, $needle)
{
    return !strncmp($haystack, $needle, strlen($needle));
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

