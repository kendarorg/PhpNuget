INSTALLATION
==============================
Supposed you install the application into the directory
    C:\IIS\PhpNuget    
Reachable with the address
    http://foo.com/my/phpnuget
    
* Create the "C:\IIS\PhpNuget\db" folder assigning write/modify rights to its content.    
* Create the "C:\IIS\PhpNuget\sources" folder assigning write/modify rights to its content
* Add, if not present, the upload_tmp_dir in php.ini and set it to the location that 
  will be used as temporary place for uploads
* Navigate on 
    http://foo.com/my/phpnuget/setup.php 
  and compile at least the password and username fields
* Delete, or rename the file C:\IIS\PhpNuget\setup.php
* Apache: If you use an apache web server remember to change the file
    C:\IIS\PhpNuget\.htaccess 
  modifying the: "web/phpnuget" with your root. 
  For our example we will change the "web/phpnuget" in .htaccess with: "my/phpnuget"
* IIS: If you run it on windows download the windowsextra.zip for the support of UrlRewrite 
  in windows and change accordingly the web.config
  

Note that the db and source location depends from the C:\IIS\PhpNuget\settings.php file. A good 
behaviour would be placing these folders -outside- the web-accessible zones!!

RELEASE NOTES
==============================

V.2.1
------------------------------
* Fixed bug for html characters in nuspec fields
* Fixed bug for carriage return in nuspec fields
* Added support for URL http://foo.bar/api/v2/package/?package=puppet returning
  the most recent version of the package
* Moved the .htaccess on the root

V.2.0
------------------------------
* Fixed error calling http://foo.bar/nuget/FindPackageById()?id='puppet'
* Some rework on classes structure

TODO
==============================
* Support for direct calls:
    http://foo.bar/api/v2/package/puppet
    http://foo.bar/api/v2/package/puppet.1.0.0.0