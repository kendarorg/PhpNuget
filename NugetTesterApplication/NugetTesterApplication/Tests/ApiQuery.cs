using NugetTesterApplication.Common;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace NugetTesterApplication.Tests
{
    public class ApiQuery : TestBase
    {
        [TestClassSetup]
        public void Setup()
        {
            CleanUpPackages();

            PushPackage("APack.1.0.0.2");
            PushPackage("APack.1.0.0.3");
            PushPackage("APack.1.0.0.12");
            PushPackage("APack.1.0.0.13");

            PushPackage("MyPackage.3.0.0-alpha4CMT");
            PushPackage("MyPackage.3.0.0-beta-1-1CMT");
        }

        [TestClassCleanup]
        public void Cleanup()
        {
            CleanUpPackages();
        }


        [TestMethod]
        public void OrderByShouldBeConsistent()
        {

            var xml = this.GetRequest("api/v2/Search()?includePrerelease=true").ToXml();
            var founded = xml.FindXmlNodes("feed", "entry").ToArray();

            Assert(founded.Count() == 2, "Wrong items count");
            Assert(founded[0].FindXmlNodes("title").First().InnerText == "APack", "Wrong package 1");
            Assert(founded[0].FindXmlNodes("m:properties", "d:Version").First().InnerText == "1.0.0.13", "Wrong package id 1");
            Assert(founded[1].FindXmlNodes("title").First().InnerText == "MyPackage", "Wrong package 2");
            Assert(founded[1].FindXmlNodes("m:properties", "d:Version").First().InnerText == "3.0.0-beta-1-1CMT", "Wrong package id 2");
        }

        [TestMethod]
        public void SearchReleaseShouldWork()
        {

            var xml = this.GetRequest("api/v2/Search()?$filter=Id eq 'MyPackage'&includePrerelease=true").ToXml();
            var founded = xml.FindXmlNodes("feed", "entry").ToArray();

            Assert(founded.Count() == 1, "Wrong items count");
            Assert(founded[0].FindXmlNodes("title").First().InnerText == "MyPackage", "Wrong package 2");
            Assert(founded[0].FindXmlNodes("m:properties", "d:Version").First().InnerText == "3.0.0-beta-1-1CMT", "Wrong package id 2");
        }

        [TestMethod]
        public void SearchShouldWork()
        {
            var xml = this.GetRequest("api/v2/Search()?$filter=Id eq 'MyPackage'&includePrerelease=false").ToXml();
            var founded = xml.FindXmlNodes("feed", "entry").ToArray();

            Assert(founded.Count() == 0, "Wrong items count");
        }

        [TestMethod]
        public void SpecialTest()
        {
            try
            {
                this.DeleteRequest("api/v2/package/{0}/{1}?apiKey={2}&setPrerelease", "APack", "1.0.0.13", NugetToken);
                this.DeleteRequest("api/v2/package/{0}/{1}?apiKey={2}&setPrerelease", "APack", "1.0.0.12", NugetToken);
                this.DeleteRequest("api/v2/package/{0}/{1}?apiKey={2}&setPrerelease", "APack", "1.0.0.3", NugetToken);
                this.DeleteRequest("api/v2/package/{0}/{1}?apiKey={2}", "APack", "1.0.0.3", NugetToken);

                var xml = this.GetRequest("api/v2/Search()?$filter=Id eq 'APack'&includePrerelease=false").ToXml();
                var founded = xml.FindXmlNodes("feed", "entry").ToArray();

                Assert(founded.Count() == 1, "Wrong items count");
                Assert(founded[0].FindXmlNodes("title").First().InnerText == "APack", "Wrong package 2");
                Assert(founded[0].FindXmlNodes("m:properties", "d:Version").First().InnerText == "1.0.0.2", "Wrong package id 2");

                xml = this.GetRequest("api/v2/Search()?$filter=Id eq 'APack'&includePrerelease=true").ToXml();
                founded = xml.FindXmlNodes("feed", "entry").ToArray();

                Assert(founded.Count() == 1, "Wrong items count");
                Assert(founded[0].FindXmlNodes("title").First().InnerText == "APack", "Wrong package 2");
                Assert(founded[0].FindXmlNodes("m:properties", "d:Version").First().InnerText == "1.0.0.13", "Wrong package id 2");
            }
            finally
            {
                this.PostRequest("api/v2/package/{0}/{1}?apiKey={2}&setPrerelease", "", "APack", "1.0.0.13", NugetToken);
                this.PostRequest("api/v2/package/{0}/{1}?apiKey={2}&setPrerelease", "", "APack", "1.0.0.12", NugetToken);
                this.PostRequest("api/v2/package/{0}/{1}?apiKey={2}&setPrerelease", "", "APack", "1.0.0.3", NugetToken);
                this.PostRequest("api/v2/package/{0}/{1}?apiKey={2}", "", "APack", "1.0.0.3", NugetToken);


            }
        }

        [TestMethod]
        public void FindPackageByIdShouldShowOnlyList()
        {
            try
            {
                this.DeleteRequest("api/v2/package/{0}/{1}?apiKey={2}&setPrerelease", "APack", "1.0.0.13", NugetToken);
                this.DeleteRequest("api/v2/package/{0}/{1}?apiKey={2}&setPrerelease", "APack", "1.0.0.12", NugetToken);
                this.DeleteRequest("api/v2/package/{0}/{1}?apiKey={2}&setPrerelease", "APack", "1.0.0.3", NugetToken);
                this.DeleteRequest("api/v2/package/{0}/{1}?apiKey={2}", "APack", "1.0.0.3", NugetToken);

                var xml = this.GetRequest("api/v2/FindPackagesById?Id=APack").ToXml();
                var founded = xml.FindXmlNodes("feed", "entry").ToArray();

                Assert(founded.Count() == 1, "Wrong items count");
                Assert(founded[0].FindXmlNodes("title").First().InnerText == "APack", "Wrong package 2");
                Assert(founded[0].FindXmlNodes("m:properties", "d:Version").First().InnerText == "1.0.0.2", "Wrong package id 2");
            }
            finally
            {
                this.PostRequest("api/v2/package/{0}/{1}?apiKey={2}&setPrerelease", "", "APack", "1.0.0.13", NugetToken);
                this.PostRequest("api/v2/package/{0}/{1}?apiKey={2}&setPrerelease", "", "APack", "1.0.0.12", NugetToken);
                this.PostRequest("api/v2/package/{0}/{1}?apiKey={2}&setPrerelease", "", "APack", "1.0.0.3", NugetToken);
                this.PostRequest("api/v2/package/{0}/{1}?apiKey={2}", "", "APack", "1.0.0.3", NugetToken);


            }
        }
    }
}