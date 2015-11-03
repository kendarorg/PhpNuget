using NugetTesterApplication.Common;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace NugetTesterApplication.Tests
{
    public class ApiSearch : TestBase
    {
        [TestClassSetup]
        public void Setup()
        {
            //CleanUp data db
            var dbfile = Path.Combine(PhpSrc, "data", "db", "nugetdb_pkg.txt");
            if (File.Exists(dbfile)) File.Delete(dbfile);

            PushPackage("NUnitTestAdapter.WithFramework.2.0.0");

            PushPackage("Microsoft.AspNet.WebPages.Data.3.2.0");
            PushPackage("Microsoft.AspNet.WebPages.Data.3.2.2");
            PushPackage("Microsoft.AspNet.WebPages.Data.3.2.3-beta1");

            PushPackage("MyPackage.3.0.0-alpha4CMT");
            PushPackage("MyPackage.3.0.0-beta-1-1CMT");
        }

        [TestClassCleanup]
        public void Cleanup()
        {
            //CleanUp data db
            var dbfile = Path.Combine(PhpSrc, "data", "db", "nugetdb_pkg.txt");
            if (File.Exists(dbfile)) File.Delete(dbfile);
        }


        [TestMethod]
        public void Vs2103StableMostDownloadNoSearch()
        {
            var res = this.GetRequest("api/v2/Search()/$count?$filter=IsLatestVersion&searchTerm=''&targetFramework='net45'&includePrerelease=false");
            Assert(res == "2", "Wrong items count");

            var xml = this.GetRequest("api/v2/Search()?$filter=IsLatestVersion&searchTerm=''&targetFramework='net45'&includePrerelease=false").ToXml();
            var founded = xml.FindXmlNodes("feed", "entry").ToArray();

            Assert(founded.Count() == 2, "Wrong items count");
            Assert(founded[0].FindXmlNodes("title").First().InnerText == "Microsoft.AspNet.WebPages.Data", "Wrong package 1");
            Assert(founded[0].FindXmlNodes( "m:properties", "d:Version").First().InnerText == "3.2.2", "Wrong package id 1");
            Assert(founded[1].FindXmlNodes("title").First().InnerText == "NUnitTestAdapter.WithFramework", "Wrong package 2");
            Assert(founded[1].FindXmlNodes( "m:properties", "d:Version").First().InnerText == "2.0.0", "Wrong package id 2");
        }

        [TestMethod]
        public void Vs2103IncludePreReleaseMostDownloadNoSearch()
        {
            var res = this.GetRequest("api/v2/Search()/$count?$filter=IsLatestVersion&searchTerm=''&targetFramework='net45'&includePrerelease=true");
            Assert(res == "3", "Wrong items count");

            var xml = this.GetRequest("api/v2/Search()?$filter=IsLatestVersion&searchTerm=''&targetFramework='net45'&includePrerelease=true").ToXml();
            var founded = xml.FindXmlNodes("feed", "entry").ToArray();

            Assert(founded.Count() == 3, "Wrong items count");
            Assert(founded[0].FindXmlNodes("title").First().InnerText == "Microsoft.AspNet.WebPages.Data", "Wrong package 1");
            Assert(founded[0].FindXmlNodes("m:properties", "d:Version").First().InnerText == "3.2.3-beta1", "Wrong package id 1");
            Assert(founded[2].FindXmlNodes("title").First().InnerText == "NUnitTestAdapter.WithFramework", "Wrong package 2");
            Assert(founded[2].FindXmlNodes("m:properties", "d:Version").First().InnerText == "2.0.0", "Wrong package id 2");
        }


        [TestMethod]
        public void Vs2103StableMostDownloadWithSearch()
        {
            var res = this.GetRequest("api/v2/Search()/$count?$filter=IsLatestVersion&searchTerm='WebPages'&targetFramework='net45'&includePrerelease=false");
            Assert(res == "1", "Wrong items count");

            var xml = this.GetRequest("api/v2/Search()?$filter=IsLatestVersion&searchTerm='WebPages'&targetFramework='net45'&includePrerelease=false").ToXml();
            var founded = xml.FindXmlNodes("feed", "entry").ToArray();

            Assert(founded.Count() == 1, "Wrong items count");
            Assert(founded[0].FindXmlNodes("title").First().InnerText == "Microsoft.AspNet.WebPages.Data", "Wrong package 1");
            Assert(founded[0].FindXmlNodes("m:properties", "d:Version").First().InnerText == "3.2.2", "Wrong package id 1");
        }

        [TestMethod]
        public void Vs2103IncludePreReleaseMostDownloadSearch()
        {
            var res = this.GetRequest("api/v2/Search()/$count?$filter=IsLatestVersion&searchTerm='WebPages'&targetFramework='net45'&includePrerelease=true");
            Assert(res == "1", "Wrong items count");

            var xml = this.GetRequest("api/v2/Search()?$filter=IsLatestVersion&searchTerm='WebPages'&targetFramework='net45'&includePrerelease=true").ToXml();
            var founded = xml.FindXmlNodes("feed", "entry").ToArray();

            Assert(founded.Count() == 1, "Wrong items count");
            Assert(founded[0].FindXmlNodes("title").First().InnerText == "Microsoft.AspNet.WebPages.Data", "Wrong package 1");
            Assert(founded[0].FindXmlNodes("m:properties", "d:Version").First().InnerText == "3.2.3-beta1", "Wrong package id 1");
        }

        [TestMethod]
        [TestIgnore("Should consider the splitted search terms in and")]
        public void Vs2103SplittedSearchTerm()
        {
            var res = this.GetRequest("api/v2/Search()/$count?$filter=IsLatestVersion&searchTerm='Web Pages'&targetFramework='net45'&includePrerelease=false");
            Assert(res == "1", "Wrong items count");

            var xml = this.GetRequest("api/v2/Search()?$filter=IsLatestVersion&searchTerm='Web Pages'&targetFramework='net45'&includePrerelease=false").ToXml();
            var founded = xml.FindXmlNodes("feed", "entry").ToArray();

            Assert(founded.Count() == 1, "Wrong items count");
            Assert(founded[0].FindXmlNodes("title").First().InnerText == "Microsoft.AspNet.WebPages.Data", "Wrong package 1");
            Assert(founded[0].FindXmlNodes("m:properties", "d:Version").First().InnerText == "3.2.2", "Wrong package id 1");
        }

        [TestMethod]
        public void Vs2103GetUpdatesStable()
        {
            var xml = this.GetRequest("api/v2/GetUpdates()?packageIds='Microsoft.AspNet.WebPages.Data'&versions='3.2.0'&includePrerelease=false&includeAllVersions=false&targetFrameworks=''&versionConstraints=''").ToXml();
            var founded = xml.FindXmlNodes("feed", "entry").ToArray();

            Assert(founded.Count() == 1, "Wrong items count");
            Assert(founded[0].FindXmlNodes("title").First().InnerText == "Microsoft.AspNet.WebPages.Data", "Wrong package 1");
            Assert(founded[0].FindXmlNodes("m:properties", "d:Version").First().InnerText == "3.2.2", "Wrong package id 1");
        }

        [TestMethod]
        public void Vs2103GetUpdatesPrereleaseToo()
        {
            
            var xml = this.GetRequest("api/v2/GetUpdates()?packageIds='Microsoft.AspNet.WebPages.Data'&versions='3.2.0'&includePrerelease=true&includeAllVersions=false&targetFrameworks=''&versionConstraints=''").ToXml();
            var founded = xml.FindXmlNodes("feed", "entry").ToArray();

            Assert(founded.Count() == 1, "Wrong items count");
            Assert(founded[0].FindXmlNodes("title").First().InnerText == "Microsoft.AspNet.WebPages.Data", "Wrong package 1");
            Assert(founded[0].FindXmlNodes("m:properties", "d:Version").First().InnerText == "3.2.3-beta1", "Wrong package id 1");
        }

        [TestMethod]
        public void ListAndUnlistPackages()
        {
            this.DeleteRequest("api/v2/package/Microsoft.AspNet.WebPages.Data/3.2.2&apiKey={0}",NugetToken);

            var res = this.GetRequest("api/v2/Search()/$count?$filter=IsLatestVersion&searchTerm='WebPages'&targetFramework='net45'&includePrerelease=false");
            Assert(res == "1", "Wrong items count");

            var xml = this.GetRequest("api/v2/Search()?$filter=IsLatestVersion&searchTerm='WebPages'&targetFramework='net45'&includePrerelease=false").ToXml();
            var founded = xml.FindXmlNodes("feed", "entry").ToArray();

            Assert(founded.Count() == 1, "Wrong items count");
            Assert(founded[0].FindXmlNodes("title").First().InnerText == "Microsoft.AspNet.WebPages.Data", "Wrong package 1");
            Assert(founded[0].FindXmlNodes("m:properties", "d:Version").First().InnerText == "3.2.0", "Wrong package id 1");



            this.PostRequest("api/v2/package/Microsoft.AspNet.WebPages.Data/3.2.2&apiKey={0}","", NugetToken);

            res = this.GetRequest("api/v2/Search()/$count?$filter=IsLatestVersion&searchTerm='WebPages'&targetFramework='net45'&includePrerelease=false");
            Assert(res == "1", "Wrong items count");

            xml = this.GetRequest("api/v2/Search()?$filter=IsLatestVersion&searchTerm='WebPages'&targetFramework='net45'&includePrerelease=false").ToXml();
            founded = xml.FindXmlNodes("feed", "entry").ToArray();

            Assert(founded.Count() == 1, "Wrong items count");
            Assert(founded[0].FindXmlNodes("title").First().InnerText == "Microsoft.AspNet.WebPages.Data", "Wrong package 1");
            Assert(founded[0].FindXmlNodes("m:properties", "d:Version").First().InnerText == "3.2.2", "Wrong package id 1");
        }

        /* [TestMethod]
         public void Vs2103StableMostDownloadNoSearch()
         {
             var res = this.GetRequest("api/v2/Search()/$count?$filter=IsLatestVersion&searchTerm=''&targetFramework='net45'&includePrerelease=true");
             Assert(res == "3", "Wrong items count");

             var node = res.FindXmlNodes("service", "workspace", "collection").FirstOrDefault();
             Assert(node != null, "Missing packages");
             node = node.FindXmlNodes("atom:title").FirstOrDefault();
             Assert(node != null, "Missing packages atom");

             Assert(node.Value != "Packages", "Wrong atom content");
         }

         [TestMethod]
         public void LoadEmptyPackages()
         {
             var res = this.GetRequest("api/v2/Packages").ToXml();

             var node = res.FindXmlNodes("feed").FirstOrDefault();
             Assert(node != null, "Missing atom feed");
         }

         [TestMethod]
         public void LoadEmptyPackagesCount()
         {
             var res = this.GetRequest("api/v2/Packages/$count");

             Assert(res=="0", "Wrong items count");
         }*/
    }
}
