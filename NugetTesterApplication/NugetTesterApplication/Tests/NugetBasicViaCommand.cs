using NugetTesterApplication.Common;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace NugetTesterApplication.Tests
{
    public class NugetBasicViaCommand : TestBase
    {
        

        [TestSetup]
        public void Setup()
        {
            //CleanUp data db
            var dbfile = Path.Combine(PhpSrc, "data", "db", "nugetdb_pkg.txt");
            if (File.Exists(dbfile)) File.Delete(dbfile);
        }

        [TestMethod]
        public void PushPackage()
        {
            PushPackage("NUnitTestAdapter.WithFramework.2.0.0");

            var res = this.RunNuget("list", "-Source", NugetApi);
            if (!res.Output.Contains("NUnitTestAdapter.WithFramework 2.0.0"))
            {
                throw new Exception("Package not pushed!");
            }
        }

        [TestMethod]
        public void PushPackageInPreReleaseNotShownByDefault()
        {
            PushPackage("Castle.Core.3.1.0-RC");

            var res = this.RunNuget("list", "-Source", NugetApi);

            if (res.Output.Contains("Castle.Core 3.1.0"))
            {
                throw new Exception("PreRelease is visible!");
            }
        }

        [TestMethod]
        public void PushPackageInPreReleaseShownWithPrerelase()
        {
            PushPackage("Castle.Core.3.1.0-RC");

            var res = this.RunNuget("list", "-Source", NugetApi,"-Prerelease");

            if (!res.Output.Contains("Castle.Core 3.1.0"))
            {
                throw new Exception("PreRelease is not visible!");
            }
        }

        [TestMethod]
        public void ShowAllShouldShowAllVersions()
        {
            PushPackage("Castle.Core.3.1.0-RC");
            PushPackage("Castle.Core.3.1.0");
            PushPackage("Castle.Core.3.3.0");

            var res = this.RunNuget("list", "-Source", NugetApi, "-AllVersions");

            if (res.Output.Contains("Castle.Core 3.1.0"))
            {
                throw new Exception("A previous version is visible! Wrong!!");
            }
        }
    }
}
