<?php


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
	var $Id;
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
	//$VersionDownloadCount
	//$MinClientVersion
	var $Tags;
	var $LicenseUrl;
	var $LicenseNames;
	var $LicenseReportUrl;
	var $TargetFramework;
	var $Owners;
}
?>