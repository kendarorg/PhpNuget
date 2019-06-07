<?php


class SpecialIdentifiers
{
    public static $Any = "Any";
    public static $Agnostic = "Agnostic";
    public static $Unsupported = "Unsupported";
}

class PlatformIdentifiers
{
    public static $WindowsPhone = "WindowsPhone";
    public static $Windows = "Windows";
}

class FrameworkIdentifiers
{
    public static $NetCoreApp = ".NETCoreApp";
    public static $NetStandardApp = ".NETStandardApp";
    public static $NetStandard = ".NETStandard";
    public static $NetPlatform = ".NETPlatform";
    public static $DotNet = "dotnet";
    public static $Net = ".NETFramework";
    public static $NetCore = ".NETCore";
    public static $WinRT = "WinRT"; // deprecated
    public static $NetMicro = ".NETMicroFramework";
    public static $Portable = ".NETPortable";
    public static $WindowsPhone = "WindowsPhone";
    public static $Windows = "Windows";
    public static $WindowsPhoneApp = "WindowsPhoneApp";
    public static $Dnx = "DNX";
    public static $DnxCore = "DNXCore";
    public static $AspNet = "ASP.NET";
    public static $AspNetCore = "ASP.NETCore";
    public static $Silverlight = "Silverlight";
    public static $Native = "native";
    public static $MonoAndroid = "MonoAndroid";
    public static $MonoTouch = "MonoTouch";
    public static $MonoMac = "MonoMac";
    public static $XamarinIOs = "Xamarin.iOS";
    public static $XamarinMac = "Xamarin.Mac";
    public static $XamarinPlayStation3 = "Xamarin.PlayStation3";
    public static $XamarinPlayStation4 = "Xamarin.PlayStation4";
    public static $XamarinPlayStationVita = "Xamarin.PlayStationVita";
    public static $XamarinWatchOS = "Xamarin.WatchOS";
    public static $XamarinTVOS = "Xamarin.TVOS";
    public static $XamarinXbox360 = "Xamarin.Xbox360";
    public static $XamarinXboxOne = "Xamarin.XboxOne";
    public static $UAP = "UAP";
    public static $Tizen = "Tizen";
}

class FrameworkConstants
{
    public static $EmptyVersion;
    public static $MaxVersion;
    public static $Version5;
    public static $Version10;
    public static $DotNetAll;

    public static function __init()
    {
        self::$EmptyVersion = Version::Build(0, 0, 0, 0);
        self::$MaxVersion = Version::Build(PHP_INT_MAX, 0, 0, 0);
        self::$Version5 = Version::Build(5, 0, 0, 0);
        self::$Version10 = Version::Build(10, 0, 0, 0);
        self::$DotNetAll = new FrameworkRange(
            NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::$NetPlatform, FrameworkConstants::$EmptyVersion),
            NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::$NetPlatform, FrameworkConstants::$MaxVersion),
            true, true);
    }
}

class CommonFrameworks
{
    public static $Net11;
    public static $Net2;
    public static $Net35;
    public static $Net4;
    public static $Net403;
    public static $Net45;
    public static $Net451;
    public static $Net452;
    public static $Net46;
    public static $Net461;
    public static $Net462;
    public static $Net463;

    public static $NetCore45;
    public static $NetCore451;
    public static $NetCore50;

    public static $Win8;
    public static $Win81;
    public static $Win10;

    public static $SL4;
    public static $SL5;

    public static $WP7;
    public static $WP75;
    public static $WP8;
    public static $WP81;
    public static $WPA81;

    public static $Tizen3;
    public static $Tizen4;

    public static $AspNet;
    public static $AspNetCore;
    public static $AspNet50;
    public static $AspNetCore50;

    public static $Dnx;
    public static $Dnx45;
    public static $Dnx451;
    public static $Dnx452;
    public static $DnxCore;
    public static $DnxCore50;

    public static $DotNet;
    public static $DotNet50;
    public static $DotNet51;
    public static $DotNet52;
    public static $DotNet53;
    public static $DotNet54;
    public static $DotNet55;
    public static $DotNet56;

    public static $NetStandard;
    public static $NetStandard10;
    public static $NetStandard11;
    public static $NetStandard12;
    public static $NetStandard13;
    public static $NetStandard14;
    public static $NetStandard15;
    public static $NetStandard16;
    public static $NetStandard17;
    public static $NetStandard20;

    public static $NetStandardApp15;

    public static $UAP10;
    public static $NetCoreApp10;
    public static $NetCoreApp11;
    public static $NetCoreApp20;
    public static $NetCoreApp21;
    public static function __init()
    {

        CommonFrameworks::$Net11 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Net, new Version(1, 1, 0, 0));
        CommonFrameworks::$Net2 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Net, new Version(2, 0, 0, 0));
        CommonFrameworks::$Net35 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Net, new Version(3, 5, 0, 0));
        CommonFrameworks::$Net4 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Net, new Version(4, 0, 0, 0));
        CommonFrameworks::$Net403 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Net, new Version(4, 0, 3, 0));
        CommonFrameworks::$Net45 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Net, new Version(4, 5, 0, 0));
        CommonFrameworks::$Net451 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Net, new Version(4, 5, 1, 0));
        CommonFrameworks::$Net452 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Net, new Version(4, 5, 2, 0));
        CommonFrameworks::$Net46 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Net, new Version(4, 6, 0, 0));
        CommonFrameworks::$Net461 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Net, new Version(4, 6, 1, 0));
        CommonFrameworks::$Net462 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Net, new Version(4, 6, 2, 0));
        CommonFrameworks::$Net463 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Net, new Version(4, 6, 3, 0));

        CommonFrameworks::$NetCore45 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetCore, new Version(4, 5, 0, 0));
        CommonFrameworks::$NetCore451 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetCore, new Version(4, 5, 1, 0));
        CommonFrameworks::$NetCore50 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetCore, new Version(5, 0, 0, 0));

        CommonFrameworks::$Win8 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Windows, new Version(8, 0, 0, 0));
        CommonFrameworks::$Win81 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Windows, new Version(8, 1, 0, 0));
        CommonFrameworks::$Win10 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Windows, new Version(10, 0, 0, 0));

        CommonFrameworks::$SL4 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Silverlight, new Version(4, 0, 0, 0));
        CommonFrameworks::$SL5 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Silverlight, new Version(5, 0, 0, 0));

        CommonFrameworks::$WP7 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::WindowsPhone, new Version(7, 0, 0, 0));
        CommonFrameworks::$WP75 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::WindowsPhone, new Version(7, 5, 0, 0));
        CommonFrameworks::$WP8 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::WindowsPhone, new Version(8, 0, 0, 0));
        CommonFrameworks::$WP81 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::WindowsPhone, new Version(8, 1, 0, 0));
        CommonFrameworks::$WPA81 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::WindowsPhoneApp, new Version(8, 1, 0, 0));

        CommonFrameworks::$Tizen3 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Tizen, new Version(3, 0, 0, 0));
        CommonFrameworks::$Tizen4 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Tizen, new Version(4, 0, 0, 0));

        CommonFrameworks::$AspNet = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::AspNet, EmptyVersion);
        CommonFrameworks::$AspNetCore = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::AspNetCore, EmptyVersion);
        CommonFrameworks::$AspNet50 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::AspNet, Version5);
        CommonFrameworks::$AspNetCore50 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::AspNetCore, Version5);

        CommonFrameworks::$Dnx = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Dnx, EmptyVersion);
        CommonFrameworks::$Dnx45 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Dnx, new Version(4, 5, 0, 0));
        CommonFrameworks::$Dnx451 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Dnx, new Version(4, 5, 1, 0));
        CommonFrameworks::$Dnx452 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::Dnx, new Version(4, 5, 2, 0));
        CommonFrameworks::$DnxCore = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::DnxCore, EmptyVersion);
        CommonFrameworks::$DnxCore50 = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::DnxCore, Version5);

        CommonFrameworks::$DotNet
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetPlatform, EmptyVersion);
        CommonFrameworks::$DotNet50
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetPlatform, Version5);
        CommonFrameworks::$DotNet51
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetPlatform, new Version(5, 1, 0, 0));
        CommonFrameworks::$DotNet52
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetPlatform, new Version(5, 2, 0, 0));
        CommonFrameworks::$DotNet53
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetPlatform, new Version(5, 3, 0, 0));
        CommonFrameworks::$DotNet54
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetPlatform, new Version(5, 4, 0, 0));
        CommonFrameworks::$DotNet55
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetPlatform, new Version(5, 5, 0, 0));
        CommonFrameworks::$DotNet56
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetPlatform, new Version(5, 6, 0, 0));

        CommonFrameworks::$NetStandard
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetStandard, EmptyVersion);
        CommonFrameworks::$NetStandard10
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetStandard, new Version(1, 0, 0, 0));
        CommonFrameworks::$NetStandard11
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetStandard, new Version(1, 1, 0, 0));
        CommonFrameworks::$NetStandard12
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetStandard, new Version(1, 2, 0, 0));
        CommonFrameworks::$NetStandard13
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetStandard, new Version(1, 3, 0, 0));
        CommonFrameworks::$NetStandard14
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetStandard, new Version(1, 4, 0, 0));
        CommonFrameworks::$NetStandard15
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetStandard, new Version(1, 5, 0, 0));
        CommonFrameworks::$NetStandard16
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetStandard, new Version(1, 6, 0, 0));
        CommonFrameworks::$NetStandard17
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetStandard, new Version(1, 7, 0, 0));
        CommonFrameworks::$NetStandard20
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetStandard, new Version(2, 0, 0, 0));

        CommonFrameworks::$NetStandardApp15
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetStandardApp, new Version(1, 5, 0, 0));

        CommonFrameworks::$UAP10
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::UAP, Version10);

        CommonFrameworks::$NetCoreApp10
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetCoreApp, new Version(1, 0, 0, 0));
        CommonFrameworks::$NetCoreApp11
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetCoreApp, new Version(1, 1, 0, 0));
        CommonFrameworks::$NetCoreApp20
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetCoreApp, new Version(2, 0, 0, 0));
        CommonFrameworks::$NetCoreApp21
            = NuGetFramework::CcFrameworkVersion(FrameworkIdentifiers::NetCoreApp, new Version(2, 1, 0, 0));
    }
}

FrameworkConstants::__init();
CommonFrameworks::__init();