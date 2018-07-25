# PhpNuget V. 4.0.4.8

## Purpose

This is born to have my personal repository for nuget, on my cheapo PHP hosting.
With support for complex OData queries.

Verified on:

* PHP 5.3.17-IIS 7
* PHP 5.3.21-IIS 7
* PHP 5.5.8-Apache 2.0 (Windows 8 Pro)
* PHP 5.6-IIS 8
* PHP 5.4.2-Apache 2.0 (OpenSuse 13.1)
* PHP 5.3.3-Apache 2.2 (CentOS 6.5)
* PHP 7.0.14-Apache 2.4 (Windows 10 Home)

## Installation

### Notes for everybody

* When installing the MySql version the db must be present with the user configured!
* The module php_curl must be present and configured inside the php.ini

### Notes for Apache With Red Hat Linux

On Red Hat Linux the mod_rewrite must be enabled on the application directory
since most features depends on it. This can be achieved modifying

	/etc/httpd/conf/http.conf

Then on the definition of the phpnuget directory, change the AllowOverride from "None"
to "All"

From:

<pre class="brush: xml;">
	<Directory "/var/www/html/phpnugetdir">
		Options Indexes FollowSymLinks
		AllowOverride None
		Order allow,deny
		Allow from all
	</Directory>
</pre>

To:

<pre class="brush: xml;">
	<Directory "/var/www/html/phpnugetdir">
		Options Indexes FollowSymLinks
		AllowOverride All
		Order allow,deny
		Allow from all
	</Directory>
</pre>

And then restart Apache

	sudo service httpd restart

This should be enough to let everything works

### Notes for Apache on Windows

If php is unable to load curl-related functions copy the following files from the php directory to "C:\Windows\System".
You can verify this by downloading a file from the official nuget repository via the manage packages page.

* libeay32.dll
* libsasl.dll
* ssleay32.dll
* libssh2

### Prerequisites For IIS

These steps are NOT needed if your hosting already configured PHP

* Create the website with a standard web.config
* Install PHP for IIS (see at the end of this document 'Installation of PHP for IIS')
* Mark the location of *php-cgi.exe*

### Common

* Download from [Kendar.org](http://www.kendar.org/?p=/dotnet/phpnuget)
* Extract somewhere
* Copy the content of the directory "src" in the location you choose for the server. *DO NOT COPY THE WEB.CONFIG IF IN IIS*
* Enable write/read/delete access on
	* db: Directory where all databases and packages are stored
	* settings.php
	* Web.Config
	* .htaccess
* Verify to have write permissions on the "db" directory.
* Open the setup page at http://myhost/mynuget/setup.php and follow the wizard. The value will be prefilled
	* Admin UserId: the user that will be created (or updated) as admin
	* Admin Password: the password (on update will be overwritten)
	* Admin Email: ...
	* Password Regex: The regex that will be used to verify the password (default min 8 chars, max 40)
	* Password Description: The error to show when the password is not matching the regex
	* Application path: If the website is "http://host/nuget" will be "nuget". If the website is "http://host" will be empty.
	* Data Root: The directory in which the txt db will be placed. It's usually a subdir of the website but can be changed.
	* Packages Root: The directory in which the uploaded packages will be placed. It's usually a subdir of the website but can be changed.
	* php-cgi.exe: To allow the configuration of php under IIS.
	* Allow package update via Upload: Default disabled, if enabled it is possible to overwrite the packages BUT THIS IS NOT A STANDARD BEHAVIOUR.
	* Allow package delete: Default disabled. LEAVING IT ENABLED IS NOT A STANDARD BEHAVIOUR
* When using the mysql installation
	* Check the "Use mysql" and fill the configuration data
	* NOTE: Import from txt db does not yet work!!!
* If under IIS set the path of 'php-cgi.exe' (leave blank if your hosting already configured PHP)
* Change the password, email and login of the administration without worries.
* Rename the setup.php to setup.bak
* Remove write access on
	* settings.php
	* Web.Config
	* .htaccess
* Now open http://myhost/mynuget and see the gallery
* Happy Nugetting!

## Daily usage

* Configure your Visual Studio or Chocolatey to use http://myhost/mynuget/api/v2/ as repository
* To upload packages through command line:

<pre>
	nuget push mypackage.nupkg myApiKey http://myhost/mynuget/upload
</pre>

* To search items the syntax follow the OData specification (more or less). Some example
	* Id eq \'NUnit\': Search for all packages with id equals to NUnit
	* substringof(\'Microsoft\',Author): Search for all packages with Microsoft between the authors
	* Version gt \'1.0.1.0.beta\': Search for all packages with version greater than 1.0.1.0.beta
* All search blocks can be grouped with parenthesis and with 'and' and 'or' keywords.
* The keywords are
	* gt/gte: Greater/Greater equal
	* lt/lte: Lower/Lower equal
	* eq/neq: Equal/Not Equals
	* substringof: Check if the first parameter is contained in the second parameter

## Administration

### Simple user

* Must be registered to upload/modify packages
* Can only edit the packages that he uploaded
* When an user upload for the first time a package with a certain id, then all versions will be owned by the same user. No other users but the administrators could modify or upload packages with the same id.
* The token available to access the API for the upload can be regenerated by any user.
* The API token can be used with our without the curly braces.
* The users will see in their list of packages only the packages that they uploaded.

### For the administrators

* They can add/remove/enable users
* They can add/modify any package. When a package uploaded initially by another user is uploaded by the administrator, the ownership of the package will be kept the same.
* It is possible to download packages from external sources. It will be sufficent to insert the address from which the packages would be taken, specifying where should be put the identifier and the version of the package to download locally (through the @ID and @VERSION tags)
* When the database is corrupted it can be regenerated through "Refresh packages db from packages directory." mind that all the packages will become owned by the administrators.
* It would be possible to upload the packages directly on the 'db/packages' directory and then run the "Refresh packages db from packages directory.". All packages not yet present on the database will be loaded.

## The Api

The nuget API is based more or less on the OData protocol.
All of the api lsited that returns a collection support the usage of parameters

* $skip: Optional. The number of items to skip. Default to 0.
* $top: Optional. The number of items returned. Default to 10.

### Api V1

* /api/v1: Retrieves the root for the entities that will be used by the API. No parameters.
* /api/v1/package/\[package-id\]/\[package-version\]: (GET) Download the specified package. No parameters.
* /api/v1/package/\[package-id\]/\[package-version\]?apiKey=xxx&setPrerelease: (POST) Set the package as listed.
	* apiKey: Mandatory. The api key of the user. Must match the one of the user that firstly inserted the package or the user must be Admin
	* setPrerelease: Optional. If present will set the package as release (without touching the listed flag) THIS IS SPECIFIC FOR phpnuget
* /api/v1/package/\[package-id\]/\[package-version\]?apiKey=xxx&setPrerelease: (DELETE) Set the package as not listed.
	* apiKey: Mandatory. The api key of the user. Must match the one of the user that firstly inserted the package or the user must be Admin
	* setPrerelease: Optional. If present will set the package as prerelease (without touching the listed flag) THIS IS SPECIFIC FOR phpnuget
* /api/v1/$metadata: Retrieves the OData metadata, the actions allowed and the entities specifications. No parameters.
* /api/v1/FindPackagesById(): Search for packages by id, returns all packages with a certain id ordered by version descending
	* Id: Mandatory parameter, specify the identifier of the package (e.g. Angular-UI-Router)
* /api/v1/FindPackagesById()/$count: Count all packages with a certain id ordered by version descending. Same parameters as 'FindPackagesById()'
* /api/v1/Search(): Search for packages satisfyng the query with a certain id ordered by version descending
	* $filter: Optional. The query that will be used for the search, see 'Daily usage' for the syntax
	* $orderby: Optional. List of fields for the order by. e.g. 'Id desc, Version, Author asc' will order by Id descending, then by Version ascending (the default) then by Author ascending.
	* searchTerm: Optional. Equivalent to write 'substringof(\'searchTerm\',Id) or substringof(\'searchTerm\',Name)'
	* targetFramework: Optional. Target framework required, the result will contain the matching packages and the ones without framework specified.
	* includePrerelease: Optional. If set to 'true' include event the prereleases (the ones with the flag set by hand on package editing or the ones with versions that contains alphabetic characters, e.g. 1.0.0.1 is not prerelease but 1.0.beta is)
* /api/v1/Search()/$count: Count all packages satisfyng the query. Same parameters as 'FindPackagesById()'
* /api/v1/Packages(): Same as Search()
* /api/v1/Packages()/$count: Same as Search()/$count
* /api/v1/Packages: Same as Search()
* /api/v1/Packages/$count: Same as Search()/$count
* /api/v1/Packages(Id='\[package-id\]',Version='\[package-id\]'): Retruns the data for the single package

### Api V2

All v1 APIs are present, remind to replace the v1 in the previous section with v2!

* /api/v2/FindPackageById(): Same as FindPackagesById()
* /api/v2/GetUpdates(): Search for packages satisfyng the query with a certain id ordered by version descending
	* packageIds: Optional. Pipe (|) separated list of package ids
	* versions: Optional. Pipe (|) separated list of package versions, their position is matching with the ones in packageIds
	* targetFrameworks: Optional. Pipe (|) separated list of package target frameworks, their position is matching with the ones in packageIds. If the framework is not specified in the package then it will be returned anyway
	* includePrerelease: Optional. If set to 'true' include event the prereleases (the ones with the flag set by hand on package editing or the ones with versions that contains alphabetic characters, e.g. 1.0.0.1 is not prerelease but 1.0.beta is)
	* includeAllVersions: Not supported yet
	* versionConstraints: Not supported yet
* /api/v2/GetUpdates()/$count: Count all packages satisfyng the query. Same parameters as 'FindPackagesById()'
* /api/v2/$batch: Executes the queries/action following the OData batch specifications.

### Api V3

As soon as the guys from nuget defines it.
Actually is work in progress, trying to follow the "working" example on nuget.org

### Other entry points

* /packages?q=term1 term2: Given the terms passed in 'q' a search is made checking that the Id or Name of the package corespond to at least one of the term passed as parameter
* /upload: The access point to upload the packages.

## Installation of PHP for IIS

### Installation for IIS 8

Tested on Windows 8 pro X64 bit and Windows 8.1 pro X64

This guid is adapted from [iis.net](http://www.iis.net/learn/application-frameworks/install-and-configure-php-on-iis/using-php-manager-for-iis-to-setup-and-configure-php)

1. download [php for windows](http://windows.php.net/)
2. extract downloaded zip ( C:/php )
3. download [php manager[(http://phpmanager.codeplex.com/releases) for IIS ( its an extension  for managing PHP from IIS control panel )
4. open php manager and click on register new php installation
5. choose php-cgi.exe and click ok (usually is inside 'C:\Program Files (x86)\PHP\v5.3\php-cgi.exe')
6. now check phpinfo and choose error reporting
7. open your favorite code editor and type

<?php echo "Hello World !" ; ?>

and save this as hello.php ( or anything ) on c:inetpubwwwroot
and open http://localhost/hello.php

now IIS is serving PHP now in next tutorial i will show you how to set up MySQL in windows

### Features of PHP manger

* Register PHP with IIS
* Validate and properly configure existing PHP installations
* Run multiple PHP versions side by side on the same server and even within the same web site
* Check PHP runtime configuration and environment (output of phpinfo() function)
* Configure various PHP settings
* Enable or disable PHP extensions
* Remotely manage PHP configuration in php.ini file.
* Easily install, configure, manage and troubleshoot one or many PHP versions on the same IIS server.
* Get updated packages
* Support viewing the profile in gallery, "nuget like" https://www.nuget.org/profiles/kendar.org
* Delete packages (when configured)


## TODO

### Must

* Add captcha and max retries of password for users
* Support on v1 the alternate address https://www.nuget.org/v1/FeedService.svc/ for https://www.nuget.org/api/v1/

### Should

* Add robots.txt
* Import v3.0.0.0 txt database on MySQL

### Would

* Add Email token to reset users passwords
* Upload multiple nupkg
* Align the Settings::ResultsPerPage in all the views

## Updates

* 4.0.4.8
	* Fix code indentation

* 4.0.4.7
	* Fix responsive navbar to display correctly on medium and large screens

* 4.0.4.6
	* Clean up bootstrap forms by using bootstrap best practice horizontal form templates

* 4.0.3.6
	* Move copyright notice out of bottom navbar and into separate container to save vertical space

* 4.0.3.5
	* Move incorrectly placed `link` tag from document body to document head
    * Move Bootstrap CSS to `assets/css` folder for consistency

* 4.0.3.4
	* Add functionality to mobile navigation
    * Include BootstrapJS
    * Include jQuery
    * Make search form into input group for clarity
    * Link navbar brand to site home for consistency

* 4.0.2.4
	* Unify identation to tabs, size 4
	* Unify EOL characters to LF
	* Remove trainling whitespace

* 4.0.2.3
	* Construct missing bootstrap grid elements in templating files
	* Add styles for vertical row spacing
	* Unify indentation and trim whitespace

* 4.0.2.2
	* Added navbar-specific styles to `style.css`
	* removed `_navbar.php` file and integrated code into `_header.php`
	* removed `_navbar.php` include from `index.php`
	* cleaned up `_header.php` (code indentation)

* 4.0.1.2
	* remove inline styles from `index.php`
	*  add and link separate `css/style.css` file to `assets` folder

* 4.0.0.2
	* Fix bug on delete for textdb version

* 4.0.0.1
	* Correct visualization of packages on MySQL
	* Error not finding javascript files
	* Correct ordering of packages
	* Showing exception texts on file upload errors

* 4.0.0.0
	* Added MySQL as storage engine

* 3.0.12.11
	* Added "count" value on all packages requests for nuget 3.5
	* Verified compatibility with Nuget.exe 2.8, 3.4, 3.5
	* Compatibility with PHP7

* 3.0.12.10
	* Added wait dialogs for long operations (update, upload from external nuget and refresh packages)

* 3.0.12.9
	* Added support for .net 4.5.2
	* Internal refactoring for net framework selection

* 3.0.12.8
	* Fixed inconsistency on boolean parameters inside packages update
	* Fixed special case for uploading files
	* Added test application for txt based db
	* Added separation between data directory and packages directory
	* Added regex to verify the password and error description

* 3.0.12.7
	* Support for packages deletion
	* Resolved setup issue on IIS with a new URLRewrite module [managedfusion-rewriter](https://github.com/kendarorg/managedfusion-rewriter)
	* Specific override for data root
	* Support Packages(Id='AngularJS.Core',Version='1.2.14') on v1 and v2 API
	* Support for downloads count

* 3.0.12.6
	* Release notes not considered
	* Escaped license url
	* Fix on package refresh

* 3.0.12.5
	* Nuget Package Explorer compatibility: Added support for standard upload
	* Nuget Package Explorer compatibility: Added support $inlinecount parameter (nuget 2.8)
	* Fix on path initialization
	* Empty packages upload not allowed anymore
	* Fixed duplicate packages upload via CLI/Nuget Package Explorer
	* Fixed "Listed" flag inconsistencies
	* Fixed "Group by" clause in database
	* Added favicon to the website
	* Default of includePrerelease to false
	* Added define('__ALLOWPACKAGEUPDATE__', true/false) in settings.php to deny (default) or allow the packages update
	* Added support for listed/unlisted Api
	* Fixed includePreRelease on Update packages

* 3.0.12.0
	* Support for $batch requests (hopefully working)
	* Support for Nuget v3 protocol (started the implementation)

* 3.0.11.0
	* Added "lazy" package refresh to avoid php timeout
	* Added version lable on footer

* 3.0.10.0
	* Users were not allowed to be made adiministrators by the administrators
	* Replaced htmlentities with htmlspecialchars
	* HttpUtils: use readfile instead of require_once

* 3.0.9.0
	* Removed typo on phpnugetobjectsearch.php

* 3.0.8.0
	* Modified the hint to register Api Key
	* Added download link while browsing packages

* 3.0.7.0
	* Support for ne/neq (thanks petero!)
	* Support for null value in OData queries
	* Aligned to semantic versioning for pre-releases (http://semver.org/)
	* Fixed a bug for the OData queries not recognizing external types
	* Uploading a package with "-" into the name will result in a pre-release package
	* Field "Is Pre Release" is now honoured correctly
	* Fixed bug for free text fields not correctly shown on packages list when containing html tags
	* Some typos
	* Fixed issue when searching for not standard queries from gallery, fallback to all words under or clause

* 3.0.6.0
	* Added "tolower" function (thanks petero-dk!)
	* Added "startswith", "endswith" and "toupper"
	* Fixed issue when calling functions passing a field name as second parameter.
	* Added tests for substringof and tolower

* 3.0.5.0
	* Fixed bug caused by wrong order of execution of sort, group by, select in Db engine

* 3.0.0.0
	* UI improved with AngularJS
	* Improved the search engine with better OData query support
	* Changed the database fields (manual import only)
	* Downloading of packages from other repositories
	* Better support for the standard nuget server behaviours

* 2.0.0.0
	* Added support for GetPackageById
	* General improvements and bugfixing
	* Support for installation on IIS

* 1.0.0.0
	* Added interface to edit packages
	* Added mod_rewrite support via .htaccess
	* Simplified the whole structure of the program
