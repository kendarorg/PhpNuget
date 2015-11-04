using HtmlAgilityPack;
using NugetTesterApplication.Common;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Configuration;
using System.Net;
using System.Reflection;
using System.Text;
using System.Threading.Tasks;
using MySql.Data.MySqlClient;
using System.Data;
using Dapper;

namespace NugetTesterApplication
{
    class Program
    {
        const string TORUN = "NugetBasicViaCommand";// ApiSearch  NugetPackageExplorer ApiQuery NugetBasicViaCommand

        const string NUGET_EXE = "nuget.exe";
        const string SAMPLES_DIR = "samples";
        const string DATA_DIR = "data";
        const string RESULTS_DIR = "results";
        private static string _phpsrc;

        static string GetNugetDirectory()
        {
            try
            {
                string codeBase = Assembly.GetExecutingAssembly().CodeBase;
                UriBuilder uri = new UriBuilder(codeBase);
                string path = Uri.UnescapeDataString(uri.Path);
                //slndir/prjdir/bin/debug
                var dir = Path.GetDirectoryName(path);
                if (File.Exists(Path.Combine(dir, NUGET_EXE)))
                {
                    return dir;
                }
                //slndir/prjdir/bin
                dir = Path.GetDirectoryName(dir);
                if (File.Exists(Path.Combine(dir, NUGET_EXE)))
                {
                    return dir;
                }
                //slndir/prjdir
                dir = Path.GetDirectoryName(dir);
                if (File.Exists(Path.Combine(dir, NUGET_EXE)))
                {
                    return dir;
                }
                //slndir
                dir = Path.GetDirectoryName(dir);
                if (File.Exists(Path.Combine(dir, NUGET_EXE)))
                {
                    return dir;
                }
            }
            catch (Exception ex)
            {

            }
            throw new Exception("missing nuget.exe!");
        }

        static void DoInvoke<T>(Object target)
        {
            try
            {
                var classSetup = target.GetType().GetMethods()
                          .FirstOrDefault(m => m.GetCustomAttributes(typeof(T), false).Length > 0);
                if (classSetup != null) classSetup.Invoke(target, new object[] { });
            }
            catch (Exception ex)
            {
                Console.WriteLine(ex);
            }
        }

        static void Main(string[] args)
        {
            var host = (!string.IsNullOrWhiteSpace(ConfigurationSettings.AppSettings["NugetUrl"]) ? ConfigurationSettings.AppSettings["NugetUrl"] : "http://localhost:8020/phpnuget").Trim().TrimEnd('/');
            var api = host + "/api/v2";
            var directory = !string.IsNullOrWhiteSpace(ConfigurationSettings.AppSettings["NugetExeDir"]) ? ConfigurationSettings.AppSettings["NugetExeDir"] : GetNugetDirectory().Trim().TrimEnd('\\');



            var userKey = SetupApplication(host, _phpsrc);

            var baseType = typeof(TestBase);
            var types = Assembly.GetExecutingAssembly()
                .GetTypes()
                .Where(p => baseType.IsAssignableFrom(p) && p != baseType);

            foreach (var type in types)
            {
                if (TORUN != "")
                {
                    if (TORUN != type.Name) continue;
                }
                var test = (TestBase)Activator.CreateInstance(type);
                test.NugetExe = Path.Combine(directory, NUGET_EXE);
                test.SamplesDir = Path.Combine(directory, SAMPLES_DIR);
                test.ResultsDir = Path.Combine(directory, RESULTS_DIR);
                test.DataDir = Path.Combine(directory, DATA_DIR);
                test.NugetToken = userKey;
                test.PhpSrc = _phpsrc;
                test.NugetApi = api;
                test.NugetHost = host;

                Console.WriteLine("Running test '" + type.Name + "'");

                DoInvoke<TestClassSetup>(test);

                var testMethods = type.GetMethods()
                          .Where(m => m.GetCustomAttributes(typeof(TestMethod), false).Length > 0)
                          .Where(m => m.GetCustomAttributes(typeof(TestIgnore), false).Length == 0);

                DoTest(testMethods, test);

                var testIgnore = type.GetMethods()
                          .Where(m => m.GetCustomAttributes(typeof(TestMethod), false).Length > 0)
                          .Where(m => m.GetCustomAttributes(typeof(TestIgnore), false).Length > 0);
                DoIgnoreTest(testIgnore, test);


                DoInvoke<TestClassCleanup>(test);

                Console.WriteLine("");
            }

            Console.ReadKey();
        }

        private static void DoIgnoreTest(IEnumerable<MethodInfo> testIgnore, TestBase test)
        {
            foreach (var meth in testIgnore)
            {
                var ignore = (TestIgnore)meth.GetCustomAttributes(typeof(TestIgnore), false).First();
                Console.WriteLine("\tIG " + meth.Name + " (" + ignore.Message);
            }
        }

        private static string SetupApplication(string host, string phpsrc)
        {
            var request = (HttpWebRequest)WebRequest.Create(host+"/setup.php");
            var response = (HttpWebResponse)request.GetResponse();
            var responseString = new StreamReader(response.GetResponseStream()).ReadToEnd();

            HtmlDocument doc = new HtmlDocument();
            doc.LoadHtml(responseString);
            foreach (HtmlNode input in doc.DocumentNode.SelectNodes("//input"))
            {
                if (input.Attributes["id"] != null && input.Attributes["id"].Value.ToLowerInvariant() == "applicationroot")
                {
                    _phpsrc = input.Attributes["value"].Value;
                }
            }


            var dbType = ConfigurationSettings.AppSettings["DbType"].ToLowerInvariant();
            if (dbType == "mysql")
            {
                using (IDbConnection connection = new MySqlConnection(ConfigurationManager.ConnectionStrings["phpnuget"].ConnectionString))
                {
                    string query = "TRUNCATE TABLE nugetdb_pkg";
                    connection.Execute(query);
                    query = "TRUNCATE TABLE nugetdb_usrs";
                    connection.Execute(query);
                }
            }
            else
            {
                var dbfile = Path.Combine(_phpsrc, "data", "db", "nugetdb_pkg.txt");
                if (File.Exists(dbfile)) File.Delete(dbfile);
                var usersFile = Path.Combine(_phpsrc, "data", "db", "nugetdb_usrs.txt");
                if (File.Exists(usersFile)) File.Delete(usersFile);

            }
            

            DirectoryInfo packs = new DirectoryInfo(Path.Combine(_phpsrc, "data", "packages"));

            foreach (FileInfo file in packs.GetFiles())
            {
                file.Delete();
            }


            request = (HttpWebRequest)WebRequest.Create(host + "/setup.php");
            var postData = new List<string>();
            postData.Add("applicationPath=" + ConfigurationSettings.AppSettings["ApplicationPath"]);
            postData.Add("dataRoot=" + Path.Combine(_phpsrc, "data", "db"));
            postData.Add("packagesRoot=" + Path.Combine(_phpsrc, "data", "packages"));
            postData.Add("dosetup=importUsers");
            postData.Add("email=nuget@localhost");
            postData.Add("login=admin");
            postData.Add("password=password");
            postData.Add("phpCgi=");
            postData.Add("servertype=apache");
            postData.Add("packageDelete=on");
            postData.Add("packageUpdate=on");

            if (dbType == "mysql")
            {
                var cs = ConfigurationManager.ConnectionStrings["phpnuget"].ConnectionString;
                var css = cs.Split(new []{';'}, StringSplitOptions.RemoveEmptyEntries);
                var props = new Dictionary<string,string>();
                foreach (var item in css)
                {
                    var iss = item.Split(new []{'='}, StringSplitOptions.RemoveEmptyEntries);
                    props.Add(iss[0], iss[1]);
                }
                
                postData.Add("mySqlLogin=" + props["Uid"]);
                postData.Add("mySqlPassword=" + props["Pwd"]);
                postData.Add("mySqlServer=" + props["Server"]);
                postData.Add("mySqlDb=" + props["Database"]);
                postData.Add("useMySql=on");
            }
            else
            {
                postData.Add("useMySql=off");
            }
       
            var data = Encoding.ASCII.GetBytes(string.Join("&",postData));

            request.Method = "POST";
            request.ContentType = "application/x-www-form-urlencoded";
            request.ContentLength = data.Length;

            using (var stream = request.GetRequestStream())
            {
                stream.Write(data, 0, data.Length);
            }

            response = (HttpWebResponse)request.GetResponse();

            responseString = new StreamReader(response.GetResponseStream()).ReadToEnd();

            if (dbType == "mysql")
            {
                using (IDbConnection connection = new MySqlConnection(ConfigurationManager.ConnectionStrings["phpnuget"].ConnectionString))
                {
                    string query = "SELECT Token FROM nugetdb_usrs WHERE UserId='admin'";
                    return connection.Query<string>(query).FirstOrDefault().Trim('{','}');
                }
            }
            else
            {
                var users = Path.Combine(_phpsrc, "data", "db", "nugetdb_usrs.txt");
                foreach (var line in File.ReadAllLines(users))
                {
                    if (line.StartsWith("s:5:\"admin\";:|"))
                    {
                        var expl = line.Split(new string[] { ":|:" }, StringSplitOptions.None)[7];
                        //s:38:"{42F65835-CEA8-BC39-CC64-8D24B4E5A816}";

                        var start = expl.IndexOf("{") + 1;
                        return expl.Substring(start, "8FDA101E-4D23-D4B7-C2BB-B1C588781D76".Length);
                    }
                }
            }
            throw new Exception("User not created!");
        }

        private static void DoTest(IEnumerable<MethodInfo> testMethods, TestBase target)
        {
            var sb = new StringBuilder();
            int success = 0;
            int error = 0;
            int total = 0;
            foreach (var testMethod in testMethods)
            {
                total++;
                try
                {
                    DoInvoke<TestSetup>(target);
                    testMethod.Invoke(target, new object[] { });
                    DoInvoke<TestCleanup>(target);
                    success++;
                    sb.AppendLine("\tOK " + testMethod.Name);
                }
                catch (Exception ex)
                {
                    error++;
                    sb.AppendLine("\tKO " + testMethod.Name);
                    sb.AppendLine("\t\t" + ex.Message);
                }
            }
            Console.WriteLine("\t{0}/{1}", success, total);
            Console.Write(sb.ToString());
        }
    }
}
