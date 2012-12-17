<?php
define('__ROOT__',dirname( dirname(__FILE__)));

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

class NugetEntity
{
    var $Version;
    var $Title;
    var $IconUrl;
    var $LicenseUrl;
    var $ProjectUrl;
    var $DownloadCount;
    var $RequireLicenseAcceptance;
    var $Description;
    var $ReleaseNotes;
    var $Published;
    var $Dependencies;
    var $PackageHash;
    var $PackageHashAlgorithm;
    var $PackageSize;
    var $Copyright;
    var $Tags;
    var $IsAbsoluteLatestVersion;
    var $IsLatestVersion;
    var $Listed;
    var $VersionDownloadCount;
    var $Author;
    var $Identifier;
    var $References;
}
?>