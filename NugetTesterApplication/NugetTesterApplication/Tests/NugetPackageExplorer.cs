using NugetTesterApplication.Common;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace NugetTesterApplication.Tests
{
    public class NugetPackageExplorer : TestBase
    {
        

        [TestSetup]
        public void Setup()
        {
            //CleanUp data db
            var dbfile = Path.Combine(PhpSrc, "data", "db", "nugetdb_pkg.txt");
            if (File.Exists(dbfile)) File.Delete(dbfile);
        }

        [TestCleanup]
        public void Cleanup()
        {
            //CleanUp data db
            var dbfile = Path.Combine(PhpSrc, "data", "db", "nugetdb_pkg.txt");
            if (File.Exists(dbfile)) File.Delete(dbfile);
        }

        [TestMethod]
        public void PushPackage()
        {
            //var res = this.RunNuget("push", Path.Combine(SamplesDir, complete), NugetToken, "-Source", NugetHost + "/upload");
            var path = Path.Combine(SamplesDir, "Microsoft.AspNet.WebPages.Data.3.2.0.nupkg");


            var res = this.UploadRequest("api/v2/package", path);
            Assert(res=="","Package not pushed!"+res);
        }
        
        [TestMethod]
        [TestIgnore]
        public void ShouldConsiderInlineCountAllpages()
        {
            ///phpnuget/api/v2/Packages()?$orderby=DownloadCount%20desc&$filter=IsAbsoluteLatestVersion&$skip=0&$top=15&$select=Id,Version,Authors,DownloadCount,VersionDownloadCount,PackageHash,PackageSize,Published&$inlinecount=allpages
        }

        [TestMethod]
        [TestIgnore]
        public void ShouldConsiderIsAbsoluteLatestVersion()
        {
            //SHOW PRE RELASE FROM NPE
            ///phpnuget/api/v2/Packages()?$orderby=DownloadCount%20desc&$filter=IsAbsoluteLatestVersion&$skip=0&$top=15&$select=Id,Version,Authors,DownloadCount,VersionDownloadCount,PackageHash,PackageSize,Published&$inlinecount=allpages
        }

        [TestMethod]
        [TestIgnore]
        public void ShouldConsiderIsLatestVersion()
        {
            //DO NOT SHOW PRE RELASE FROM NPE
            ///phpnuget/api/v2/Packages()?$orderby=DownloadCount%20desc&$filter=IsLatestVersion&$skip=0&$top=15&$select=Id,Version,Authors,DownloadCount,VersionDownloadCount,PackageHash,PackageSize,Published&$inlinecount=allpages
        }

        /*[TestMethod]
        public void PushPackageInPreReleaseShownWithPrerelase()
        {
            PushPackage("Microsoft.AspNet.WebPages.Data.3.2.3-beta1");
            PushPackage("Microsoft.AspNet.WebPages.Data.3.2.2");

            var res = this.RunNuget("list", "-Source", NugetApi,"-Prerelease");

            Assert(res.Output.Contains("Microsoft.AspNet.WebPages.Data 3.2.3-beta1"), "PreRelease is not visible!" + res.Output);
        }

        [TestMethod]
        public void ShowAllShouldShowAllVersions()
        {
            PushPackage("Microsoft.AspNet.WebPages.Data.3.2.3-beta1");
            PushPackage("Microsoft.AspNet.WebPages.Data.3.2.3");
            PushPackage("Microsoft.AspNet.WebPages.Data.3.2.2");

            var res = this.RunNuget("list", "-Source", NugetApi, "-AllVersions");

            Assert(!res.Output.Contains("Microsoft.AspNet.WebPages.Data.3.2.2"), "A previous version is visible! Wrong!!" + res.Output);
        }*/
    }
}
