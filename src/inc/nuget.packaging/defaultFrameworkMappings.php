<?php

class DefaultFrameworkMappings
{
    public static $IdentifierSynonyms = array();
    public static $IdentifierShortNames = array();
    public static $ProfileShortNames = array();
    public static $NuGetFramework = array();
    public static $EquivalentFrameworks = array();
    public static $EquivalentProfiles = array();
    public static $SubSetFrameworks = array();
    public static $CompatibilityMappings = array();

    public static $NonPackageBasedFrameworkPrecedence = array();
    public static $PackageBasedFrameworkPrecedence = array();
    public static $EquivalentFrameworkPrecedence = array();

    public static $ShortNameReplacements = array();
    public static $FullNameReplacements = array();

    public static function FillIdentifierSynonims()
    {
        // .NET
        DefaultFrameworkMappings::$IdentifierSynonyms["NETFramework"] = FrameworkIdentifiers::$Net;
        DefaultFrameworkMappings::$IdentifierSynonyms[".NET"] = FrameworkIdentifiers::$Net;

        // .NET Core
        DefaultFrameworkMappings::$IdentifierSynonyms["NETCore"] = FrameworkIdentifiers::$NetCore;

        // Portable
        DefaultFrameworkMappings::$IdentifierSynonyms["NETPortable"] = FrameworkIdentifiers::$Portable;

        // ASP
        DefaultFrameworkMappings::$IdentifierSynonyms["asp.net"] = FrameworkIdentifiers::$AspNet;
        DefaultFrameworkMappings::$IdentifierSynonyms["asp.netcore"] = FrameworkIdentifiers::$AspNetCore;

        // Mono/Xamarin
        DefaultFrameworkMappings::$IdentifierSynonyms["Xamarin.PlayStationThree"] = FrameworkIdentifiers::$XamarinPlayStation3;
        DefaultFrameworkMappings::$IdentifierSynonyms["XamarinPlayStationThree"] = FrameworkIdentifiers::$XamarinPlayStation3;
        DefaultFrameworkMappings::$IdentifierSynonyms["Xamarin.PlayStationFour"] = FrameworkIdentifiers::$XamarinPlayStation4;
        DefaultFrameworkMappings::$IdentifierSynonyms["XamarinPlayStationFour"] = FrameworkIdentifiers::$XamarinPlayStation4;
        DefaultFrameworkMappings::$IdentifierSynonyms["XamarinPlayStationVita"] = FrameworkIdentifiers::$XamarinPlayStationVita;
    }

    public static function FillIdentifierShortNames()
    {
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$NetCoreApp] = "netcoreapp";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$NetStandardApp] = "netstandardapp";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$NetStandard] = "netstandard";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$NetPlatform] = "dotnet";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$Net] = "net";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$NetMicro] = "netmf";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$Silverlight] = "sl";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$Portable] = "portable";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$WindowsPhone] = "wp";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$WindowsPhoneApp] = "wpa";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$Windows] = "win";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$AspNet] = "aspnet";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$AspNetCore] = "aspnetcore";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$Native] = "native";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$MonoAndroid] = "monoandroid";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$MonoTouch] = "monotouch";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$MonoMac] = "monomac";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$XamarinIOs] = "xamarinios";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$XamarinMac] = "xamarinmac";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$XamarinPlayStation3] = "xamarinpsthree";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$XamarinPlayStation4] = "xamarinpsfour";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$XamarinPlayStationVita] = "xamarinpsvita";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$XamarinWatchOS] = "xamarinwatchos";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$XamarinTVOS] = "xamarintvos";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$XamarinXbox360] = "xamarinxboxthreesixty";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$XamarinXboxOne] = "xamarinxboxone";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$Dnx] = "dnx";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$DnxCore] = "dnxcore";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$NetCore] = "netcore";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$WinRT] = "winrt"; // legacy
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$UAP] = "uap";
        DefaultFrameworkMappings::$IdentifierShortNames[FrameworkIdentifiers::$Tizen] = "tizen";
    }

    public static function FillProfileShortNames()
    {
        DefaultFrameworkMappings::$ProfileShortNames[] = new FrameworkSpecificMapping(FrameworkIdentifiers::$Net, "Client", "Client");
        DefaultFrameworkMappings::$ProfileShortNames[] = new FrameworkSpecificMapping(FrameworkIdentifiers::$Net, "CF", "CompactFramework");
        DefaultFrameworkMappings::$ProfileShortNames[] = new FrameworkSpecificMapping(FrameworkIdentifiers::$Net, "Full", "");
        DefaultFrameworkMappings::$ProfileShortNames[] = new FrameworkSpecificMapping(FrameworkIdentifiers::$Silverlight, "WP", "WindowsPhone");
        DefaultFrameworkMappings::$ProfileShortNames[] = new FrameworkSpecificMapping(FrameworkIdentifiers::$Silverlight, "WP71", "WindowsPhone71");
    }

    public static function FillEquivalentFrameworks()
    {
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$UAP, FrameworkConstants::$EmptyVersion),
            CommonFrameworks::$UAP10);

        // win <-> win8
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$Windows, FrameworkConstants::$EmptyVersion),
            CommonFrameworks::$Win8);

        // win8 <-> netcore45
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            CommonFrameworks::$Win8,
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$NetCore, new Version(4, 5, 0, 0)));

        // netcore45 <-> winrt45
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$NetCore, new Version(4, 5, 0, 0)),
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$WinRT, new Version(4, 5, 0, 0)));

        // netcore <-> netcore45
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$NetCore, FrameworkConstants::$EmptyVersion),
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$NetCore, new Version(4, 5, 0, 0)));

        // winrt <-> winrt45
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$WinRT, FrameworkConstants::$EmptyVersion),
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$WinRT, new Version(4, 5, 0, 0)));

        // win81 <-> netcore451
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            CommonFrameworks::$Win81,
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$NetCore, new Version(4, 5, 1, 0)));

        // wp <-> wp7
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$WindowsPhone, FrameworkConstants::$EmptyVersion),
            CommonFrameworks::$WP7);

        // wp7 <-> f:sl3-wp
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            CommonFrameworks::$WP7,
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$Silverlight, new Version(3, 0, 0, 0), "WindowsPhone"));

        // wp71 <-> f:sl4-wp71
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$WindowsPhone, new Version(7, 1, 0, 0)),
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$Silverlight, new Version(4, 0, 0, 0), "WindowsPhone71"));

        // wp8 <-> f:sl8-wp
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            CommonFrameworks::$WP8,
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$Silverlight, new Version(8, 0, 0, 0), "WindowsPhone"));

        // wp81 <-> f:sl81-wp
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            CommonFrameworks::$WP81,
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$Silverlight, new Version(8, 1, 0, 0), "WindowsPhone"));

        // wpa <-> wpa81
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$WindowsPhoneApp, FrameworkConstants::$EmptyVersion),
            CommonFrameworks::$WPA81);

        // tizen <-> tizen3
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$Tizen, FrameworkConstants::$EmptyVersion),
            CommonFrameworks::$Tizen3);

        // dnx <-> dnx45
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            CommonFrameworks::$Dnx,
            CommonFrameworks::$Dnx45);

        // dnxcore <-> dnxcore50
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            CommonFrameworks::$DnxCore,
            CommonFrameworks::$DnxCore50);

        // dotnet <-> dotnet50
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            CommonFrameworks::$DotNet,
            CommonFrameworks::$DotNet50);

        // TODO: remove these rules post-RC
        // aspnet <-> aspnet50
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            CommonFrameworks::$AspNet,
            CommonFrameworks::$AspNet50);

        // aspnetcore <-> aspnetcore50
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            CommonFrameworks::$AspNetCore,
            CommonFrameworks::$AspNetCore50);

        // dnx451 <-> aspnet50
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            CommonFrameworks::$Dnx45,
            CommonFrameworks::$AspNet50);

        // dnxcore50 <-> aspnetcore50
        DefaultFrameworkMappings::$EquivalentFrameworks[] = array(
            CommonFrameworks::$DnxCore50,
            CommonFrameworks::$AspNetCore50);
    }

    public static function FillEquivalentProfiles()
    {
        DefaultFrameworkMappings::$EquivalentProfiles[] = new FrameworkSpecificMapping(FrameworkIdentifiers::$Net, "Client", "");
        DefaultFrameworkMappings::$EquivalentProfiles[] = new FrameworkSpecificMapping(FrameworkIdentifiers::$Net, "Full", "");
        DefaultFrameworkMappings::$EquivalentProfiles[] = new FrameworkSpecificMapping(FrameworkIdentifiers::$Silverlight, "WindowsPhone71", "WindowsPhone");
        DefaultFrameworkMappings::$EquivalentProfiles[] = new FrameworkSpecificMapping(FrameworkIdentifiers::$WindowsPhone, "WindowsPhone71", "WindowsPhone");
    }

    public static function FillSubSetFrameworks()
    {
        DefaultFrameworkMappings::$SubSetFrameworks[FrameworkIdentifiers::$Net] = FrameworkIdentifiers::$Dnx;
        DefaultFrameworkMappings::$SubSetFrameworks[FrameworkIdentifiers::$NetPlatform] = FrameworkIdentifiers::$DnxCore;
        DefaultFrameworkMappings::$SubSetFrameworks[FrameworkIdentifiers::$NetStandard] = FrameworkIdentifiers::$NetStandardApp;
    }

    public static function FillCompatibilityMappings()
    {
        // UAP supports Win81
        DefaultFrameworkMappings::$CompatibilityMappings[] = new OneWayCompatibilityMappingEntry(new FrameworkRange(
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$UAP, FrameworkConstants::$EmptyVersion),
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$UAP, FrameworkConstants::$MaxVersion), true, true),
            new FrameworkRange(
                NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$Windows, FrameworkConstants::$EmptyVersion),
                NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$Windows, new Version(8, 1, 0, 0)), true, true));

        // UAP supports WPA81
        DefaultFrameworkMappings::$CompatibilityMappings[] = new OneWayCompatibilityMappingEntry(new FrameworkRange(
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$UAP, FrameworkConstants::$EmptyVersion),
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$UAP, FrameworkConstants::$MaxVersion), true, true),
            new FrameworkRange(
                NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$WindowsPhoneApp, FrameworkConstants::$EmptyVersion),
                NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$WindowsPhoneApp, new Version(8, 1, 0, 0)), true, true));

        // UAP supports NetCore50
        DefaultFrameworkMappings::$CompatibilityMappings[] = new OneWayCompatibilityMappingEntry(new FrameworkRange(
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$UAP, FrameworkConstants::$EmptyVersion),
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$UAP, FrameworkConstants::$MaxVersion), true, true),
            new FrameworkRange(
                NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$NetCore, FrameworkConstants::$Version5),
                NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$NetCore, FrameworkConstants::$Version5), true, true));

        // Win projects support WinRT
        DefaultFrameworkMappings::$CompatibilityMappings[] = new OneWayCompatibilityMappingEntry(new FrameworkRange(
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$Windows, FrameworkConstants::$EmptyVersion),
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$Windows, FrameworkConstants::$MaxVersion), true, true),
            new FrameworkRange(
                NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$WinRT, FrameworkConstants::$EmptyVersion),
                NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$WinRT, new Version(4, 5, 0, 0)), true, true));

        // Tizen3 projects support NETStandard1.6
        DefaultFrameworkMappings::$CompatibilityMappings[] = DefaultFrameworkMappings::CreateStandardMapping(
            CommonFrameworks::$Tizen3,
            CommonFrameworks::$NetStandard16);

        // Tizen4 projects support NETStandard2.0
        DefaultFrameworkMappings::$CompatibilityMappings[] = DefaultFrameworkMappings::CreateStandardMapping(
            CommonFrameworks::$Tizen4,
            CommonFrameworks::$NetStandard20);

        // UAP 10.0.15064.0 projects support NETStandard2.0
        DefaultFrameworkMappings::$CompatibilityMappings[] = DefaultFrameworkMappings::CreateStandardMapping(
            NugetFramework::CcFrameworkVersion(FrameworkIdentifiers::$UAP, new Version(10, 0, 15064, 0)),
            CommonFrameworks::$NetStandard20);

        // NetCoreApp1.0 projects support NetStandard1.6
        DefaultFrameworkMappings::$CompatibilityMappings[] = DefaultFrameworkMappings::CreateStandardMapping(
            CommonFrameworks::$NetCoreApp10,
            CommonFrameworks::$NetStandard16);

        // NetCoreApp1.1 projects support NetStandard1.7
        DefaultFrameworkMappings::$CompatibilityMappings[] = DefaultFrameworkMappings::CreateStandardMapping(
            CommonFrameworks::$NetCoreApp11,
            CommonFrameworks::$NetStandard17);

        // NetCoreApp2.0 projects support NetStandard2.0
        DefaultFrameworkMappings::$CompatibilityMappings[] = DefaultFrameworkMappings::CreateStandardMapping(
            CommonFrameworks::$NetCoreApp20,
            CommonFrameworks::$NetStandard20);

        // net463 projects support NetStandard2.0
        DefaultFrameworkMappings::$CompatibilityMappings[] = DefaultFrameworkMappings::CreateStandardMapping(
            CommonFrameworks::$Net463,
            CommonFrameworks::$NetStandard20);

        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMappingForAllVersions(
            FrameworkIdentifiers::$DnxCore,
            CommonFrameworks::$DotNet56,
            CommonFrameworks::$NetStandard15));

        // uap -> dotnet5.5, netstandard1.4
        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMappingForAllVersions(
            FrameworkIdentifiers::$UAP,
            CommonFrameworks::$DotNet55,
            CommonFrameworks::$NetStandard14));

        // netcore50 -> dotnet5.5, netstandard1.4
        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMapping(
            CommonFrameworks::$NetCore50,
            CommonFrameworks::$DotNet55,
            CommonFrameworks::$NetStandard14));

        // wpa81 -> dotnet5.3, netstandard1.2
        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMapping(
            CommonFrameworks::$WPA81,
            CommonFrameworks::$DotNet53,
            CommonFrameworks::$NetStandard12));

        // wp8, wp81 -> dotnet5.1, netstandard1.0
        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMapping(
            CommonFrameworks::$WP8,
            CommonFrameworks::$DotNet51,
            CommonFrameworks::$NetStandard10));

        // net45 -> dotnet5.2, netstandard1.1
        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMapping(
            CommonFrameworks::$Net45,
            CommonFrameworks::$DotNet52,
            CommonFrameworks::$NetStandard11));

        // net451 -> dotnet5.3, netstandard1.2
        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMapping(
            CommonFrameworks::$Net451,
            CommonFrameworks::$DotNet53,
            CommonFrameworks::$NetStandard12));

        // net46 -> dotnet5.4, netstandard1.3
        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMapping(
            CommonFrameworks::$Net46,
            CommonFrameworks::$DotNet54,
            CommonFrameworks::$NetStandard13));

        // net461 -> dotnet5.5, netstandard2.0
        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMapping(
            CommonFrameworks::$Net461,
            CommonFrameworks::$DotNet55,
            CommonFrameworks::$NetStandard20));

        // net462 -> dotnet5.6, netstandard2.0
        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMapping(
            CommonFrameworks::$Net462,
            CommonFrameworks::$DotNet56,
            CommonFrameworks::$NetStandard20));

        // netcore45 -> dotnet5.2, netstandard1.1
        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMapping(
            CommonFrameworks::$NetCore45,
            CommonFrameworks::$DotNet52,
            CommonFrameworks::$NetStandard11));

        // netcore451 -> dotnet5.3, netstandard1.2
        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMapping(
            CommonFrameworks::$NetCore451,
            CommonFrameworks::$DotNet53,
            CommonFrameworks::$NetStandard12));

        // xamarin frameworks
        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMappingForAllVersions(
            FrameworkIdentifiers::$MonoAndroid,
            CommonFrameworks::$DotNet56,
            CommonFrameworks::$NetStandard20));

        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMappingForAllVersions(
            FrameworkIdentifiers::$MonoMac,
            CommonFrameworks::$DotNet56,
            CommonFrameworks::$NetStandard20));

        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMappingForAllVersions(
            FrameworkIdentifiers::$MonoTouch,
            CommonFrameworks::$DotNet56,
            CommonFrameworks::$NetStandard20));

        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMappingForAllVersions(
            FrameworkIdentifiers::$XamarinIOs,
            CommonFrameworks::$DotNet56,
            CommonFrameworks::$NetStandard20));

        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMappingForAllVersions(
            FrameworkIdentifiers::$XamarinMac,
            CommonFrameworks::$DotNet56,
            CommonFrameworks::$NetStandard20));

        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMappingForAllVersions(
            FrameworkIdentifiers::$XamarinPlayStation3,
            CommonFrameworks::$DotNet56,
            CommonFrameworks::$NetStandard20));

        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMappingForAllVersions(
            FrameworkIdentifiers::$XamarinPlayStation4,
            CommonFrameworks::$DotNet56,
            CommonFrameworks::$NetStandard20));

        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMappingForAllVersions(
            FrameworkIdentifiers::$XamarinPlayStationVita,
            CommonFrameworks::$DotNet56,
            CommonFrameworks::$NetStandard20));

        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMappingForAllVersions(
            FrameworkIdentifiers::$XamarinXbox360,
            CommonFrameworks::$DotNet56,
            CommonFrameworks::$NetStandard20));

        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMappingForAllVersions(
            FrameworkIdentifiers::$XamarinXboxOne,
            CommonFrameworks::$DotNet56,
            CommonFrameworks::$NetStandard20));

        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMappingForAllVersions(
            FrameworkIdentifiers::$XamarinTVOS,
            CommonFrameworks::$DotNet56,
            CommonFrameworks::$NetStandard20));

        DefaultFrameworkMappings::AddCompatibilityMappings(DefaultFrameworkMappings::CreateGenerationAndStandardMappingForAllVersions(
            FrameworkIdentifiers::$XamarinWatchOS,
            CommonFrameworks::$DotNet56,
            CommonFrameworks::$NetStandard20));
    }

    private static function CreateGenerationMapping(
        $framework,
        $netPlatform)
    {
        return new OneWayCompatibilityMappingEntry(
            new FrameworkRange(
                $framework,
                NugetFramework::CcFrameworkVersion($framework->Framework, FrameworkConstants::$MaxVersion), true, true),
            new FrameworkRange(
                CommonFrameworks::$DotNet,
                $netPlatform, true, true));
    }

    public static function CreateGenerationAndStandardMapping(
        $framework,
        $netPlatform,
        $netStandard)
    {
        $result = array();
        $result[] = DefaultFrameworkMappings::CreateGenerationMapping($framework, $netPlatform);
        $result[] = DefaultFrameworkMappings::CreateStandardMapping($framework, $netStandard);
        return $result;
    }

    public static function CreateGenerationAndStandardMappingForAllVersions(
        $framework,
        $netPlatform,
        $netStandard)
    {
        $lowestFramework = NuGetFramework::CcFrameworkVersion($framework, FrameworkConstants::$EmptyVersion);
        return DefaultFrameworkMappings::CreateGenerationAndStandardMapping($lowestFramework, $netPlatform, $netStandard);
    }

    public static function AddCompatibilityMappings($ar)
    {
        foreach ($ar as $i) {
            DefaultFrameworkMappings::$CompatibilityMappings[] = $i;
        }
    }

    public static function CreateStandardMapping(
        $framework,
        $netPlatform)
    {
        return new OneWayCompatibilityMappingEntry(
            new FrameworkRange(
                $framework,
                NuGetFramework::CcFrameworkVersion($framework->Framework, FrameworkConstants::$MaxVersion), true, true),
            new FrameworkRange(
                CommonFrameworks::$NetStandard10,
                $netPlatform, true, true));
    }


    public static function __init()
    {
        DefaultFrameworkMappings::FillIdentifierSynonims();
        DefaultFrameworkMappings::FillIdentifierShortNames();
        DefaultFrameworkMappings::FillProfileShortNames();
        DefaultFrameworkMappings::FillEquivalentFrameworks();
        DefaultFrameworkMappings::FillEquivalentProfiles();
        DefaultFrameworkMappings::FillSubSetFrameworks();
        DefaultFrameworkMappings::FillCompatibilityMappings();

        DefaultFrameworkMappings::$NonPackageBasedFrameworkPrecedence[] = FrameworkIdentifiers::$Net;
        DefaultFrameworkMappings::$NonPackageBasedFrameworkPrecedence[] = FrameworkIdentifiers::$NetCore;
        DefaultFrameworkMappings::$NonPackageBasedFrameworkPrecedence[] = FrameworkIdentifiers::$Windows;
        DefaultFrameworkMappings::$NonPackageBasedFrameworkPrecedence[] = FrameworkIdentifiers::$WindowsPhoneApp;

        DefaultFrameworkMappings::$PackageBasedFrameworkPrecedence[] = FrameworkIdentifiers::$NetCoreApp;
        DefaultFrameworkMappings::$PackageBasedFrameworkPrecedence[] = FrameworkIdentifiers::$NetStandardApp;
        DefaultFrameworkMappings::$PackageBasedFrameworkPrecedence[] = FrameworkIdentifiers::$NetStandard;
        DefaultFrameworkMappings::$PackageBasedFrameworkPrecedence[] = FrameworkIdentifiers::$NetPlatform;

        DefaultFrameworkMappings::$EquivalentFrameworkPrecedence[] = FrameworkIdentifiers::$Windows;
        DefaultFrameworkMappings::$EquivalentFrameworkPrecedence[] = FrameworkIdentifiers::$NetCore;
        DefaultFrameworkMappings::$EquivalentFrameworkPrecedence[] = FrameworkIdentifiers::$WinRT;
        DefaultFrameworkMappings::$EquivalentFrameworkPrecedence[] = FrameworkIdentifiers::$WindowsPhone;
        DefaultFrameworkMappings::$EquivalentFrameworkPrecedence[] = FrameworkIdentifiers::$Silverlight;
        DefaultFrameworkMappings::$EquivalentFrameworkPrecedence[] = FrameworkIdentifiers::$DnxCore;
        DefaultFrameworkMappings::$EquivalentFrameworkPrecedence[] = FrameworkIdentifiers::$AspNetCore;
        DefaultFrameworkMappings::$EquivalentFrameworkPrecedence[] = FrameworkIdentifiers::$Dnx;
        DefaultFrameworkMappings::$EquivalentFrameworkPrecedence[] = FrameworkIdentifiers::$AspNet;

        DefaultFrameworkMappings::$ShortNameReplacements[CommonFrameworks::$DotNet50] = CommonFrameworks::$DotNet;
        DefaultFrameworkMappings::$FullNameReplacements[CommonFrameworks::$DotNet] = CommonFrameworks::$DotNet50;
        DefaultFrameworkMappings::$FullNameReplacements[CommonFrameworks::$DotNet] = CommonFrameworks::$DotNet50;
    }
}

DefaultFrameworkMappings::__init();
?>