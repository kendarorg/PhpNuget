<?php

namespace lib\nuget;

class NugetUtils
{
    public function verifyNetFw($fw,$shortFw,$tf,$sep){
        $output_array = array();
        $result = preg_match('/\.'.$fw.'(\d)(?:\.(\d))?(?:\.(\d))?/', $tf, $output_array);
        if($result == 1 && $result != false){
            $toret = $sep.$shortFw;
            if(sizeof($output_array)>=2){
                $toret.=$output_array[1];
            }
            if(sizeof($output_array)>=3){
                $toret.=$sep.$output_array[2];
            }
            if(sizeof($output_array)>=4){
                $toret.=$sep.$output_array[3];
            }
            return $toret;
        }
        return null;
    }
    /**
     * @see https://docs.microsoft.com/it-it/nuget/reference/target-frameworks
     * @param $tf  .netframework4.6.2 | natvie | xxxx
     * @return string
     */
    public function translateNetVersion($tf)
    {
        $tf = strtolower($tf);
        $checkFw = verifyNetFw("netframework","net",$tf,"");
        if($checkFw!=null) return $checkFw;

        return trim($tf,".");
    }

    public static function buildSplitVersion($v){

        $blocks= explode("-",$v);
        $beta = sizeof($blocks)>=2?join("-",array_slice($blocks,1)):"";
        $number = explode(".",$blocks[0]);

        while(sizeof($number)<4){
            array_insert($number,"0",0);
        }

        $newData = array();
        $newData[] = trim($number[0],"'");
        $newData[] = trim($number[1],"'");
        $newData[] = trim($number[2],"'");
        $newData[] =trim($number[3],"'");
        $newData[] = trim($beta,"'");
        return $newData;
    }

    public static function isPreRelease($version)
    {
        $version = strtolower($version);
        $tmp = indexOf($version,"-");
        return $tmp>0;
    }
}