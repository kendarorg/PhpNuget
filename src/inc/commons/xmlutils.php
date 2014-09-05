<?php
require_once(dirname(__FILE__)."/utils.php");

function XML2ArrayGetKeyOrArray($xmlArray,$key)
{
    $toret = array();
    if(array_key_exists($key,$xmlArray)){
        if(is_assoc_array($xmlArray[$key])){
            $toret[] =   $xmlArray[$key]; 
        }else{
            $toret = $xmlArray[$key];
        }
    }
    return $toret;
}

function XML2Array ( $xml , $recursive = false )
{
    if ( ! $recursive ){
        $array = simplexml_load_string ( $xml ) ;
    } else {
        $array = $xml ;
    }
   
    $newArray = array () ;
    $array = ( array ) $array ;
    foreach ( $array as $key => $value ){   
        //echo $key."\n";
        
        //echo "---\n";
        $value = ( array ) $value ;
        if(is_string($value)){
            $newArray [ strtolower ($key) ] = trim($value) ;
        }else if (!is_assoc_array($value ) && isset($value [0]) && sizeof($value)==1){
            
            $newArray [ strtolower ($key) ] = trim ( $value[0] ) ;
        }/*else if (!is_assoc_array($value ) && isset($value [0]) && sizeof($value)==1){
            $subArray = array();
            foreach($value as $subValue){
              $subArray[] = XML2Array ( $value , true ) ;
            }
            $newArray [ strtolower ($key) ] = $subArray ;
        }*/ else {
            //echo "AAA".$key."\n";
            //print_r($value);
            $newArray [ strtolower ($key) ] = XML2Array ( $value , true ) ;
        }
    }
    return $newArray ;
}

?>