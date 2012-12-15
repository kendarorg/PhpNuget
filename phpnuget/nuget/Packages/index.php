<?php
define('__ROOT__',dirname(dirname( dirname(__FILE__))));
require_once(__ROOT__.'/inc/virtualdirectory.php'); 
$virtualDirectory = new VirtualDirectory();
$baseUrl = $virtualDirectory->baseurl;
$baseUrl = $virtualDirectory->upFromLevel($baseUrl,2);
header("Content-Type: text/xml");
echo "<?xml version='1.0' encoding='utf-8' standalone='yes'?>";
?>
<feed xml:base="<?php echo $baseUrl;?>/nuget/" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" 
    xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns="http://www.w3.org/2005/Atom">
  <title type="text">Packages</title>
  <id><?php echo $baseUrl;?>/nuget/Packages</id>
  <updated>2012-12-13T19:00:52Z</updated>
  <link rel="self" title="Packages" href="Packages" />
  <entry>
    <id><?php echo $baseUrl;?>/nuget/Packages(Id='Microsoft.Web.Infrastructure',Version='1.0.0.0')</id>
    <title type="text">Microsoft.Web.Infrastructure</title>
    <summary type="text"></summary>
    <updated>2012-12-13T18:58:15Z</updated>
    <author>
      <name>Microsoft</name>
    </author>
    <link rel="edit-media" title="Package" href="Packages(Id='Microsoft.Web.Infrastructure',Version='1.0.0.0')/$value" />
    <link rel="edit" title="Package" href="Packages(Id='Microsoft.Web.Infrastructure',Version='1.0.0.0')" />
    <category term="NuGet.Server.DataServices.Package" scheme="http://schemas.microsoft.com/ado/2007/08/dataservices/scheme" />
    <content type="application/zip" src="<?php echo $baseUrl;?>/api/v2/package/microsoft.web.infrastructure/1.0.0.0" />
    <m:properties xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices">
      <d:Version>1.0.0.0</d:Version>
      <d:Title>Microsoft.Web.Infrastructure</d:Title>
      <d:IconUrl m:null="true"></d:IconUrl>
      <d:LicenseUrl>http://go.microsoft.com/fwlink/?LinkID=214339</d:LicenseUrl>
      <d:ProjectUrl>http://www.asp.net/</d:ProjectUrl>
      <d:DownloadCount m:type="Edm.Int32">0</d:DownloadCount>
      <d:RequireLicenseAcceptance m:type="Edm.Boolean">false</d:RequireLicenseAcceptance>
      <d:Description>This package contains the Microsoft.Web.Infrastructure assembly that lets you dynamically register HTTP modules at run time.</d:Description>
      <d:ReleaseNotes m:null="true"></d:ReleaseNotes>
      <d:Published m:type="Edm.DateTime">2012-12-13T18:58:34.2410871Z</d:Published>
      <d:Dependencies></d:Dependencies>
      <d:PackageHash>FNmvLn5m2LTU/Rs2KWVo0SIIh9Ek+U0ojex7xeDaSHw/zgEP77A8vY5cVWgUtBGS8MJfDGNn8rpXJWEIQaPwTg==</d:PackageHash>
      <d:PackageHashAlgorithm>SHA512</d:PackageHashAlgorithm>
      <d:PackageSize m:type="Edm.Int64">24921</d:PackageSize>
      <d:Copyright m:null="true"></d:Copyright>
      <d:Tags xml:space="preserve"> ASPNETWEBPAGES </d:Tags>
      <d:IsAbsoluteLatestVersion m:type="Edm.Boolean">true</d:IsAbsoluteLatestVersion>
      <d:IsLatestVersion m:type="Edm.Boolean">true</d:IsLatestVersion>
      <d:Listed m:type="Edm.Boolean">false</d:Listed>
      <d:VersionDownloadCount m:type="Edm.Int32">0</d:VersionDownloadCount>
      <d:Summary m:null="true"></d:Summary>
    </m:properties>
  </entry>
</feed>