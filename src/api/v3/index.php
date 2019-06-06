<?php
require_once(dirname(__FILE__) . "/../../root.php");
require_once(__ROOT__ . "/settings.php");
require_once(__ROOT__ . "/inc/api_users.php");
require_once(__ROOT__ . "/inc/commons/url.php");
require_once(__ROOT__ . "/inc/commons/http.php");

header('Content-Type: application/json');
?>
{
"version": "3.0.0",
"resources": [
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3/query",
"@type": "SearchQueryService",
"comment": "Query endpoint of NuGet Search service"
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3/autocomplete",
"@type": "SearchAutocompleteService",
"comment": "Autocomplete endpoint of NuGet Search service"
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3",
"@type": "SearchGalleryQueryService/3.0.0-rc",
"comment": "Azure Website based Search Service used by Gallery"
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3/registration/1.0.0",
"@type": "RegistrationsBaseUrl",
"comment": "Base URL of Azure storage where NuGet package registration info is stored"
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3/container/",
"@type": "PackageBaseAddress/3.0.0",
"comment": "Base URL of where NuGet packages are stored, in the format <?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3/container/{id-lower}/{version-lower}/{id-lower}.{version-lower}.nupkg"
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v2",
"@type": "LegacyGallery"
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v2",
"@type": "LegacyGallery/2.0.0"
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v2/package",
"@type": "PackagePublish/2.0.0"
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3/query",
"@type": "SearchQueryService/3.0.0-rc",
"comment": "Query endpoint of NuGet Search service used by RC clients"
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3/autocomplete",
"@type": "SearchAutocompleteService/3.0.0-rc",
"comment": "Autocomplete endpoint of NuGet Search service (primary) used by RC clients"
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3/registration/1.0.0",
"@type": "RegistrationsBaseUrl/3.0.0-rc",
"comment": "Base URL of Azure storage where NuGet package registration info is stored used by RC clients. This base URL does not include SemVer 2.0.0 packages."
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>?specialType=reportAbuse&id={id}&version={version}",
"@type": "ReportAbuseUriTemplate/3.0.0-rc",
"comment": "URI template used by NuGet Client to construct Report Abuse URL for packages used by RC clients"
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3/registration/1.0.0/{id-lower}/index.json",
"@type": "PackageDisplayMetadataUriTemplate/3.0.0-rc",
"comment": "URI template used by NuGet Client to construct display metadata for Packages using ID"
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3/registration3/1.0.0/{id-lower}/{version-lower}.json",
"@type": "PackageVersionDisplayMetadataUriTemplate/3.0.0-rc",
"comment": "URI template used by NuGet Client to construct display metadata for Packages using ID, Version"
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3/query",
"@type": "SearchQueryService/3.0.0-beta",
"comment": "Query endpoint of NuGet Search service used by beta clients"
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3/autocomplete",
"@type": "SearchAutocompleteService/3.0.0-beta",
"comment": "Autocomplete endpoint of NuGet Search service used by beta clients"
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3/registration/1.0.0",
"@type": "RegistrationsBaseUrl/3.0.0-beta",
"comment": "Base URL of Azure storage where NuGet package registration info is stored used by Beta clients. This base URL does not include SemVer 2.0.0 packages."
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>?specialType=reportAbuse&id={id}&version={version}",
"@type": "ReportAbuseUriTemplate/3.0.0-beta",
"comment": "URI template used by NuGet Client to construct Report Abuse URL for packages"
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3/registration/1.0.0/",
"@type": "RegistrationsBaseUrl/3.4.0",
"comment": "Base URL of Azure storage where NuGet package registration info is stored in GZIP format. This base URL does not include SemVer 2.0.0 packages."
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3/registration/2.0.0/",
"@type": "RegistrationsBaseUrl/3.6.0",
"comment": "Base URL of Azure storage where NuGet package registration info is stored in GZIP format. This base URL includes SemVer 2.0.0 packages."
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3/registration/2.0.0/",
"@type": "RegistrationsBaseUrl/Versioned",
"clientVersion": "4.3.0-alpha",
"comment": "Base URL of Azure storage where NuGet package registration info is stored in GZIP format. This base URL includes SemVer 2.0.0 packages."
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>api/v3/catalog/index.json",
"@type": "Catalog/3.0.0",
"comment": "Index of the NuGet package catalog."
},
{
"@id": "<?php echo UrlUtils::CurrentUrl(Settings::$SiteRoot);?>?specialType=singlePackage&id={id}&version={version}",
"@type": "PackageDetailsUriTemplate/5.1.0",
"comment": "URI template used by NuGet Client to construct details URL for packages. Call directly the UI"
},
{
"@id": "https://api.nuget.org/v3-index/repository-signatures/4.7.0/index.json",
"@type": "RepositorySignatures/4.7.0",
"comment": "The endpoint for discovering information about this package source's repository signatures."
},
{
"@id": "https://api.nuget.org/v3-index/repository-signatures/5.0.0/index.json",
"@type": "RepositorySignatures/5.0.0",
"comment": "The endpoint for discovering information about this package source's repository signatures."
},
{
"@id": "https://www.nuget.org/api/v2/symbolpackage",
"@type": "SymbolPackagePublish/4.9.0",
"comment": "The gallery symbol publish endpoint."
}
],
"@context": {
"@vocab": "http://schema.nuget.org/services#",
"comment": "http://www.w3.org/2000/01/rdf-schema#comment"
}
}
