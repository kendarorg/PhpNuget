<?php
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