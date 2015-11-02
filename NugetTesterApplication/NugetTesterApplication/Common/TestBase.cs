using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace NugetTesterApplication.Common
{
    public abstract class TestBase
    {
        public string DataDir { get; set; }
        public string ResultsDir { get; set; }
        public string SamplesDir { get; set; }
        public string NugetExe { get; set; }

        public NugetResult RunNuget(params string[] args)
        {
            var realArgs = new List<string>();
            foreach (var baseArg in args)
            {
                var arg = baseArg.Trim();
                if (arg.IndexOf(" ") > 0)
                {
                    arg = "\"" + arg + "\"";
                }
                realArgs.Add(arg);
            }
            // Start the child process.
            Process p = new Process();
            // Redirect the output stream of the child process.
            p.StartInfo.UseShellExecute = false;
            p.StartInfo.RedirectStandardOutput = true;
            p.StartInfo.RedirectStandardError = true;
            p.StartInfo.FileName = NugetExe;
            p.StartInfo.Arguments = string.Join(" ",realArgs.ToArray());
            p.Start();
            // Do not wait for the child process to exit before
            // reading to the end of its redirected stream.
            // p.WaitForExit();
            // Read the output stream first and then wait.
            string output = p.StandardOutput.ReadToEnd();
            string error = p.StandardError.ReadToEnd();
            p.WaitForExit();
            return new NugetResult
            {
                Output = output,
                Error = error
            };
        }

        public string NugetToken { get; set; }

        public string NugetApi { get; set; }

        public string NugetHost { get; set; }

        public string PhpSrc { get; set; }

        public void PushPackage(string id,string version=null)
        {
            var complete = version == null ? id + ".nupkg" : id + "." + version + ".nupkg";
            var res = this.RunNuget("push", Path.Combine(SamplesDir,complete), NugetToken, "-Source", NugetHost + "/upload");
            if (!res.Output.Contains("Your package was pushed."))
            {
                throw new Exception("Package not pushed!");
            }
        }
    }
}
