Nuget V3 Protocol analisys

## Base Apis

* /api/v2/publish
* /api/v3/index.json
* /api/v3/query
* /api/v3/registration/{semver}/{packageid}/index.json
* /api/v3/registration/{semver}/{packageid}/{version}.json
* /api/v3/registration/{semver}/{packageid}/page/{from}/{to}.json
* /api/v3/container/{idLower}/{versionLower}/{fullversion}.nupkg
* /api/v3/container/{packageid}/index.json
* /api/v3/catalog/data/{date}/{fullPackage}.json
	
## Api mapping names 

See https://api.nuget.org/v3/index.json for reference	

### Semver ALL

* /v2
    * LegacyGallery
    * LegacyGallery/2.0.0
* /v2/publish
    * PackagePublish/2.0.0
* /v3/catalog
    * Catalog/3.0.0
* /v3/query
    * SearchQueryService
    * SearchQueryService/3.0.0-rc
    * SearchQueryService/3.0.0-beta
* /v3/container
    * PackageBaseAddress/3.0.0
* /v3/reportabuse
    * ReportAbuseUriTemplate
    * ReportAbuseUriTemplate/3.0.0-rc
    * ReportAbuseUriTemplate/3.0.0-beta
* /v3/autocomplete
    * SearchAutocompleteService
    * SearchAutocompleteService/3.0.0-rc
    * SearchAutocompleteService/3.0.0-beta
* /v3
    * SearchGalleryQueryService/3.0.0-rc

### Semver 1.0.0

* /v3/registration/1.0.0
    * RegistrationsBaseUrl
    * RegistrationsBaseUrl/3.4.0
    * RegistrationsBaseUrl/3.0.0-beta
    * RegistrationsBaseUrl/3.0.0-rc
    * PackageDisplayMetadataUriTemplate/3.0.0-rc
    * PackageVersionDisplayMetadataUriTemplate/3.0.0-rc

### Semver 2.0.0

* /v3/registration/2.0.0
    * RegistrationsBaseUrl/3.6.0

### Remappings

This are simple remapping when specifying the semver

#### Remappings when requesting specific service names on semver 2.0.0

* RegistrationsBaseUrl -> RegistrationsBaseUrl/3.6.0
* PackageDisplayMetadataUriTemplate -> RegistrationsBaseUrl/3.6.0
* PackageVersionDisplayMetadataUriTemplate -> RegistrationsBaseUrl/3.6.0

#### General remappings

* PackageDisplayMetadataUriTemplate -> RegistrationsBaseUrl	
* PackageVersionDisplayMetadataUriTemplate -> RegistrationsBaseUrl	
		