<?php

namespace lib\nuget\models;

class NugetPackage
{
    /**
     * @var boolean
     */
    public $IsSymbols = false;
    /**
     * @var string
     */
    public $Id;

    /**
     * @var string
     */
    public $UserId;

    /**
     * @var string
     */
    public $Version;
    /**
     * @var string[]
     */
    public $Author = array();

    /**
     * @var string
     */
    public $Copyright = "";

    /**
     * @var string
     */
    public $Created = "";

    /**
     * @var NugetDependency[]
     */
    public $Dependencies = array();

    /**
     * @var string
     */
    public $Description = "";

    /**
     * @var integer
     */
    public $DownloadCount = 0;
    //V1 ExternalPackageUrl
    //GEN GalleryDetailsUrl

    /**
     * @var string
     */
    public $IconUrl = "";

    /**
     * @var bool
     */
    public $IsLatestVersion = false;
    /**
     * @var boolean
     */
    public $Listed =true;

    /**
     * @var boolean
     */
    public $IsAbsoluteLatestVersion = false;
    /**
     * @var bool
     */
    public $IsPreRelease = false;	//V1 Prerelease

    /**
     * @var bool
     */
    public $LastUpdated = false;
    /**
     * @var string
     */
    public $PackageHash = "";
    /**
     * @var string
     */
    public $PackageHashAlgorithm="sha256";

    /**
     * @var integer
     */
    public $PackageSize = 0;
    /**
     * @var string
     */
    public $ProjectUrl = "";
    //ReportAbuseUrl
    /**
     * @var string
     */
    public $ReleaseNotes = "";
    /**
     * @var bool
     */
    public $RequireLicenseAcceptance = false;
    /**
     * @var string
     */
    public $Summary = "";
    /**
     * @var string
     */
    public $Title = "";
    /**
     * @var int
     */
    public $VersionDownloadCount = 0;
    //$MinClientVersion
    /**
     * @var string
     */
    public $Tags = "";
    /**
     * @var string
     */
    public $LicenseUrl = "";
    /**
     * @var string
     */
    public $LicenseNames = "";
    /**
     * @var string
     */
    public $LicenseReportUrl = "";
    /**
     * @var string
     */
    public $TargetFramework = "";
    /**
     * @var string[]
     */
    public $Owners =array();
    /**
     * @var string[]
     */
    public $References = array();
}