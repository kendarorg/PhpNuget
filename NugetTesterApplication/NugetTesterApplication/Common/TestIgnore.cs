using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace NugetTesterApplication.Common
{
    public class TestIgnore:Attribute
    {
        public TestIgnore(string msg)
        {
            Message = msg;
        }

        public string Message { get; private set; }
    }
}
