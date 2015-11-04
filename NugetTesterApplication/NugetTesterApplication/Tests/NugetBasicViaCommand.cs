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
            CleanUpPackages();
        }

        [TestCleanup]
        public void Cleanup()
        {
            CleanUpPackages();
        }

        [TestMethod]
        public void PushPackage()
        {
            PushPackage("NUnitTestAdapter.WithFramework.2.0.0");

            var res = this.RunNuget("list", "-Source", NugetApi);
            Assert(res.Output.Contains("NUnitTestAdapter.WithFramework 2.0.0"),"Package not pushed!"+res.Output);
        }

        [TestMethod]
        public void PushPackageAlternative()
        {
            PushPackageAlternative("NUnitTestAdapter.WithFramework.2.0.0");

            var res = this.RunNuget("list", "-Source", NugetApi);
            Assert(res.Output.Contains("NUnitTestAdapter.WithFramework 2.0.0"), "Package not pushed!" + res.Output);
        }

        [TestMethod]
        public void PushPackageAlternativeFromRoot()
        {
            PushPackageAlternative("NUnitTestAdapter.WithFramework.2.0.0",target:"");

            var res = this.RunNuget("list", "-Source", NugetApi);
            Assert(res.Output.Contains("NUnitTestAdapter.WithFramework 2.0.0"), "Package not pushed!" + res.Output);
        }

        [TestMethod]
        public void PushPackageInPreReleaseNotShownByDefault()
        {
            PushPackage("Microsoft.AspNet.WebPages.Data.3.2.3-beta1");

            var res = this.RunNuget("list", "-Source", NugetApi);

            Assert(!res.Output.Contains("Microsoft.AspNet.WebPages.Data.3.2.3-beta1"), "PreRelease is visible!" + res.Output);
        }

        [TestMethod]
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
        }
    }
}
