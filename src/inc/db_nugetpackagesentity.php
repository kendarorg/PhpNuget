<?php

function verifyNetFw($fw,$shortFw,$tf,$sep){
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
 * @param $tf  .netframework4.6.2 | natvie | xxxx
 * @return string
 */
function translateNetVersion($tf)
{
    $tf = strtolower($tf);
    $checkFw = verifyNetFw("netframework","net",$tf,"");
    if($checkFw!=null) return $checkFw;

    return trim($tf,".");
}

function buildSplitVersion($v){

	$blocks= explode("-",$v);
	$beta = sizeof($blocks)>=2?join("-",array_slice($blocks,1)):"";
	$number = explode(".",$blocks[0]);
	
	while(sizeof($number)<4){
		array_insert($number,"0",0);
	}
	
	$newData = array();
	$newData[] = ($number[0]);
	$newData[] = ($number[1]);
	$newData[] = ($number[2]);
	$newData[] =($number[3]);
	$newData[] = ($beta);
	return $newData;
}

class NugetDependency
{
    var $IsGroup=false;
    var $Id;
    var $Version;
}

class NugetDependencyGroup
{
    var $IsGroup=true;
    var $TargetFramework;
    var $Dependencies;
}

class PackageDescriptor
{
    var $IsSymbols;
	var $Id;
	var $Version0;
	var $Version1;
	var $Version2;
	var $Version3;
	var $VersionBeta;
	var $Version;
	var $Author;
	var $Copyright;
	var $Created;
	var $Dependencies;
	var $Description;
	var $DownloadCount;
	//V1 ExternalPackageUrl
	//GEN GalleryDetailsUrl
	var $IconUrl;
	var $IsLatestVersion;
	var $Listed;
	var $IsAbsoluteLatestVersion;
	var $IsPreRelease;	//V1 Prerelease
	var $LastUpdated;
	var $PackageHash;
	var $PackageHashAlgorithm="sha256";
	var $PackageSize;
	var $ProjectUrl;
	//ReportAbuseUrl
	var $ReleaseNotes;
	var $RequireLicenseAcceptance;
	var $Summary;
	var $Title;
	var $VersionDownloadCount;
	//$MinClientVersion
	var $Tags;
	var $LicenseUrl;
	var $LicenseNames;
	var $LicenseReportUrl;
	var $TargetFramework;
	var $Owners;
}
?>