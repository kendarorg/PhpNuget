<?php

function translateNetVersion($tf)
{
    $tf = strtolower($tf);
    switch($tf){
		case("native"): return "native";
		case(".netframework4.6.1"): return "net461";
		case(".netframework4.6"): return "net46";
		case(".netframework4.5.2"): return "net452";
		case(".netframework4.5.1"): return "net451";
        case(".netframework4.5"): return "net45";
        case(".netframework3.5"): return "net35";
        case(".netframework4.0"): return "net40";
        case(".netframework3.0"): return "net30";
        case(".netframework2.0"): return "net20";
        case(".netframework1.0"): return "net10";
        default: return $tf;
    }
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