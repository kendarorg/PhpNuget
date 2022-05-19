<?php

namespace lib\db\mysql;

use lib\nuget\fields\mysql\NugetPackageConverter;
use lib\nuget\models\NugetPackage;
use lib\OminousFactory;
use lib\utils\Properties;
use PHPUnit\Framework\TestCase;

class MySqlCUDTest  extends TestCase
{
    public function testDelete(): void
    {
        OminousFactory::initialize();
        $mysqliMock = new MySqlMock();
        $mysqliMock->initialize([new NugetPackage()]);
        $properties = new Properties();
        $properties->setProperty("dbtype","mysql");
        OminousFactory::setObject("mysqli",$mysqliMock);
        $nugetPackages = OminousFactory::getObject("nugetpackages");
        $nugetPackages->delete("Pack1","1.0.0.0-beta");
        $this->assertEquals(1,sizeof($mysqliMock->queries));
        $this->assertEquals("DELETE FROM packages WHERE Id='Pack1' AND Version='1.0.0.0-beta'",$mysqliMock->queries[0]);
    }

    public function testAdd(): void
    {
        OminousFactory::initialize();
        $mysqliMock = new MySqlMock();
        $mysqliMock->initialize([]); //Query
        $mysqliMock->initialize([]); //Insert
        $properties = new Properties();
        $properties->setProperty("dbtype","mysql");
        OminousFactory::setObject("mysqli",$mysqliMock);
        $nugetPackages = OminousFactory::getObject("nugetpackages");

        $data = new NugetPackage();
        $data->Id="Pack";
        $data->Version = "1.0.0.1-beta";
        $nugetPackages->update($data);
        $this->assertEquals(2,sizeof($mysqliMock->queries));
        $this->assertEquals("SELECT * FROM (SELECT * FROM packages  WHERE (Id='Pack' and Version='1.0.0.1-beta'))",$mysqliMock->queries[0]);
        $this->assertEquals("INSERT INTO packages (false,'Pack','1.0.0.1-beta','[]','','','[]','',0,'',false,true,false,false,false,'','sha256',0,'','',false,'','',0,'','','','','','[]','[]','1','0','0','1','beta') VALUES (IsSymbols,Id,Version,Author,Copyright,Created,Dependencies,Description,DownloadCount,IconUrl,IsLatestVersion,Listed,IsAbsoluteLatestVersion,IsPreRelease,LastUpdated,PackageHash,PackageHashAlgorithm,PackageSize,ProjectUrl,ReleaseNotes,RequireLicenseAcceptance,Summary,Title,VersionDownloadCount,Tags,LicenseUrl,LicenseNames,LicenseReportUrl,TargetFramework,Owners,References,Version0,Version1,Version2,Version3,VersionBeta)",$mysqliMock->queries[1]);
    }

    public function testUpdate(): void
    {
        OminousFactory::initialize();
        $mysqliMock = new MySqlMock();
        $old = new NugetPackage();
        $old->Id="Pack";
        $old->Version = "1.0.0.1-beta";
        $old->Description = "OLD";

        $cnv = new NugetPackageConverter();
        $mysqliMock->initialize([$cnv->toAssoc($old)]); //Query
        $mysqliMock->initialize([]); //Insert
        $properties = new Properties();
        $properties->setProperty("dbtype","mysql");
        OminousFactory::setObject("mysqli",$mysqliMock);
        $nugetPackages = OminousFactory::getObject("nugetpackages");

        $data = new NugetPackage();
        $data->Id="Pack";
        $data->Version = "1.0.0.1-beta";
        $data->Description ="NEW";
        $nugetPackages->update($data);
        $this->assertEquals(2,sizeof($mysqliMock->queries));
        $this->assertEquals("SELECT * FROM (SELECT * FROM packages  WHERE (Id='Pack' and Version='1.0.0.1-beta'))",$mysqliMock->queries[0]);
        $this->assertEquals("UPDATE packages SET IsSymbols=false,Id='Pack',Version='1.0.0.1-beta',Author='[]',Copyright='',Created='',Dependencies='[]',Description='NEW',DownloadCount=0,IconUrl='',IsLatestVersion=false,Listed=true,IsAbsoluteLatestVersion=false,IsPreRelease=false,LastUpdated=false,PackageHash='',PackageHashAlgorithm='sha256',PackageSize=0,ProjectUrl='',ReleaseNotes='',RequireLicenseAcceptance=false,Summary='',Title='',VersionDownloadCount=0,Tags='',LicenseUrl='',LicenseNames='',LicenseReportUrl='',TargetFramework='',Owners='[]',References='[]',Version0='1',Version1='0',Version2='0',Version3='1',VersionBeta='beta' WHERE Id='Pack' AND Version='1.0.0.1-beta'",$mysqliMock->queries[1]);
    }
}