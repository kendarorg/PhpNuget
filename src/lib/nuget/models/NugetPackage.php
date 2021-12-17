<?php

namespace lib\nuget\models;

class NugetPackage
{
    public $IsSymbols;
    public $Id;
    public $Version0;
    public $Version1;
    public $Version2;
    public $Version3;
    public $VersionBeta;
    public $Version;
    /**
     * @var array
     */
    public $Author;
    public $Copyright;
    public $Created;

    /**
     * @var NugetDependency[]
     */
    public $Dependencies;
    public $Description;
    public $DownloadCount;
    //V1 ExternalPackageUrl
    //GEN GalleryDetailsUrl
    public $IconUrl;
    public $IsLatestVersion;
    /**
     * @var bool
     */
    public $Listed;
    public $IsAbsoluteLatestVersion;
    /**
     * @var bool
     */
    public $IsPreRelease;	//V1 Prerelease
    public $LastUpdated;
    public $PackageHash;
    public $PackageHashAlgorithm="sha256";
    public $PackageSize;
    public $ProjectUrl;
    //ReportAbuseUrl
    public $ReleaseNotes;
    /**
     * @var bool
     */
    public $RequireLicenseAcceptance;
    public $Summary;
    public $Title;
    public $VersionDownloadCount;
    //$MinClientVersion
    public $Tags;
    public $LicenseUrl;
    public $LicenseNames;
    public $LicenseReportUrl;
    public $TargetFramework;
    public $Owners;
    /**
     * @var string[]
     */
    public $References;
}