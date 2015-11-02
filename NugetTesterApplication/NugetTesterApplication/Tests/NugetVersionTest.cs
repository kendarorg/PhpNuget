using NugetTesterApplication.Common;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace NugetTesterApplication.Tests
{
    public class NugetVersionTest:TestBase
    {
        [TestMethod]
        public void VerifyVersion()
        {
            var res = this.RunNuget();
            if (!res.Output.Contains("NuGet Version: 2.8"))
            {
                throw new Exception("Wrong version");
            }
        }
    }
}
