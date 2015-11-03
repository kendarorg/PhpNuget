using NugetTesterApplication.Common;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace NugetTesterApplication.Tests
{
    public class DefaultApi : TestBase
    {
        [TestMethod]
        public void LoadRoot()
        {
            var res = this.GetRequest("api/v2").ToXml();

            var node = res.FindXmlNodes("service", "workspace", "collection").FirstOrDefault();
            Assert(node != null,"Missing packages");
            node = node.FindXmlNodes("atom:title").FirstOrDefault();
            Assert(node != null, "Missing packages atom");

            Assert(node.Value != "Packages", "Wrong atom content");
        }

        [TestMethod]
        public void LoadMetadata()
        {
            var res = this.GetRequest("api/v2/$metadata").ToXml();

            var node = res.FindXmlNodes("edmx:Edmx", "edmx:DataServices", "Schema","EntityType").FirstOrDefault();
            Assert(node.Attributes["Name"] != null, "Missing entity name");
            Assert(node.Attributes["Name"].Value =="V2FeedPackage", "Wront entity name");
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
            var res = this.GetRequest("api/v2/Packages/$count").Trim();

            Assert(res=="0", "Wrong items count");
        }
    }
}
