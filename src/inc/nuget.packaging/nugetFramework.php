<?php

class NugetFramework
{
    private static function NormalizeVersion($version)
    {
        $normalized = $version;

        if ($version->Build < 0
            || $version->Revision < 0) {
            $normalized = new Version(
                $version->Major,
                $version->Minor,
                Math . Max($version->Build, 0),
                Math . Max($version->Revision, 0));
        }

        return $normalized;
    }

    public static function CcFrameworkVersion($fw, $version,$profile=null)
    {
        $res = new NugetFramework();
        $res->Framework = $fw;
        $res->Profile = $profile;
        $res->Version = NugetFramework::NormalizeVersion($version);
        return $res;
    }

    var $IsPCL;
    var $Version;
    var $IsUnsupported;
    var $IsSpecificFramework;
    var $IsPackageBased;
    var $IsAny;
    var $AllFrameworkVersions;
    var $IsAgnostic;
    var $HasProfile;
    var $ShortFolderName;
    var $Profile;
    var $Framework;
    var $DotNetFrameworkName;

    public static function Equals($x,$y){
        return $x->Version->Eq($y->Version)
            && strcasecmp($x->Framework, $y->Framework)==0
            && strcasecmp($x->Profile, $y->Profile)==0;
    }
}

?>