<?php

namespace lib\nuget\models;

class NugetPackage
{
    /**
     * @var boolean
     */
    public $IsSymbols;
    public $Id;

    /**
     * @var number
     */
    public $Version0;
    /**
     * @var number
     */
    public $Version1;
    /**
     * @var number
     */
    public $Version2;
    /**
     * @var number
     */
    public $Version3;
    /**
     * @var number
     */
    public $VersionBeta;

    public $Version;
    /**
     * @var string[]
     */
    public $Author;
    public $Copyright;

    /**
     * @var date
     */
    public $Created;

    /**
     * @var NugetDependency[]
     */
    public $Dependencies;
    public $Description;

    /**
     * @var number
     */
    public $DownloadCount;
    //V1 ExternalPackageUrl
    //GEN GalleryDetailsUrl
    public $IconUrl;
    public $IsLatestVersion;
    /**
     * @var boolean
     */
    public $Listed;

    /**
     * @var boolean
     */
    public $IsAbsoluteLatestVersion;
    /**
     * @var bool
     */
    public $IsPreRelease;	//V1 Prerelease

    /**
     * @var date
     */
    public $LastUpdated;
    public $PackageHash;
    public $PackageHashAlgorithm="sha256";

    /**
     * @var number
     */
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