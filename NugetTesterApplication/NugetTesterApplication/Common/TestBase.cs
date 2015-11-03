using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Configuration;
using System.Data;
using System.Diagnostics;
using System.IO;
using Dapper;
using System.Linq;
using System.Net;
using System.Text;
using System.Threading.Tasks;
using System.Xml;
using MySql.Data.MySqlClient;

namespace NugetTesterApplication.Common
{
    public abstract class TestBase
    {
        public string DataDir { get; set; }
        public string ResultsDir { get; set; }
        public string SamplesDir { get; set; }
        public string NugetExe { get; set; }

        public void CleanUpPackages()
        {
            var dbType = ConfigurationSettings.AppSettings["DbType"].ToLowerInvariant();
            if (dbType == "mysql")
            {
                using (IDbConnection connection = new MySqlConnection(ConfigurationManager.ConnectionStrings["phpnuget"].ConnectionString))
                {
                    string query = "TRUNCATE TABLE nugetdb_pkg";
                    connection.Execute(query);
                }
            }
            else
            {
                var dbfile = Path.Combine(PhpSrc, "data", "db", "nugetdb_pkg.txt");
                if (File.Exists(dbfile)) File.Delete(dbfile);

            }

        }

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
            p.StartInfo.Arguments = string.Join(" ", realArgs.ToArray());
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

        public void PushPackage(string id, string version = null)
        {
            var complete = version == null ? id + ".nupkg" : id + "." + version + ".nupkg";
            var res = this.RunNuget("push", Path.Combine(SamplesDir, complete), NugetToken, "-Source", NugetHost + "/upload");
            if (!res.Output.Contains("Your package was pushed."))
            {
                throw new Exception("Package not pushed!");
            }
        }

        public void Assert(bool condition, string exception)
        {
            if (!condition) throw new Exception(exception);
        }

        public string GetRequest(string format, params string[] pars)
        {
            var request = (HttpWebRequest)WebRequest.Create(NugetHost.TrimEnd('/') + "/" + string.Format(format, pars).TrimStart('/'));

            request.Method = "GET";
            var response = (HttpWebResponse)request.GetResponse();

            return new StreamReader(response.GetResponseStream()).ReadToEnd().Trim();
        }

        public string DeleteRequest(string format, params string[] pars)
        {
            var request = (HttpWebRequest)WebRequest.Create(NugetHost.TrimEnd('/') + "/" + string.Format(format, pars).TrimStart('/'));

            request.Method = "DELETE";


            var response = (HttpWebResponse)request.GetResponse();

            return new StreamReader(response.GetResponseStream()).ReadToEnd().Trim();
        }

        public string PostRequest(string format, string postData, params string[] pars)
        {
            var request = (HttpWebRequest)WebRequest.Create(NugetHost.TrimEnd('/') + "/" + string.Format(format, pars).TrimStart('/'));

            request.Method = "POST";
            var data = Encoding.ASCII.GetBytes(postData);

            using (var stream = request.GetRequestStream())
            {
                stream.Write(data, 0, data.Length);
            }

            var response = (HttpWebResponse)request.GetResponse();

            return new StreamReader(response.GetResponseStream()).ReadToEnd().Trim();
        }

        public string PostRequest(string format, byte[] data, params string[] pars)
        {
            var request = (HttpWebRequest)WebRequest.Create(NugetHost.TrimEnd('/') + "/" + string.Format(format, pars).TrimStart('/'));

            request.Method = "POST";

            request.ContentType = "multipart/form-data";
            request.Headers.Add("X-NUGET-APIKEY: " + this.NugetToken);
            //request.Headers.Add("TES_TDATA: TEST-DATA");
            request.ContentLength = data.Length;

            using (var stream = request.GetRequestStream())
            {
                stream.Write(data, 0, data.Length);
            }

            var response = (HttpWebResponse)request.GetResponse();

            return new StreamReader(response.GetResponseStream()).ReadToEnd().Trim();
        }


        public string UploadRequest(string format, string file, string method="PUT", NameValueCollection nvc = null, params string[] pars)
        {

            var url = NugetHost.TrimEnd('/') + "/" + string.Format(format, pars).TrimStart('/');

            long length = 0;
            string boundary = "-----------------------------" +
            DateTime.Now.Ticks.ToString("x");


            HttpWebRequest httpWebRequest2 = (HttpWebRequest)WebRequest.Create(url);
            httpWebRequest2.ContentType = "multipart/form-data; boundary=" +boundary;

            httpWebRequest2.Headers.Add("X-NUGET-APIKEY: " + this.NugetToken);
            httpWebRequest2.Method = method;
            httpWebRequest2.KeepAlive = true;
            httpWebRequest2.Credentials =
            System.Net.CredentialCache.DefaultCredentials;



            Stream memStream = new System.IO.MemoryStream();

            byte[] boundarybytes = System.Text.Encoding.ASCII.GetBytes("\r\n--" +
            boundary + "\r\n");


            string formdataTemplate = "\r\n--" + boundary +
            "\r\nContent-Disposition: form-data; name=\"{0}\";\r\n\r\n{1}";

            if (nvc != null)
            {
                foreach (string key in nvc.Keys)
                {
                    string formitem = string.Format(formdataTemplate, key, nvc[key]);
                    byte[] formitembytes = System.Text.Encoding.UTF8.GetBytes(formitem);
                    memStream.Write(formitembytes, 0, formitembytes.Length);
                }
            }


            memStream.Write(boundarybytes, 0, boundarybytes.Length);

            string headerTemplate = "Content-Disposition: form-data; name=\"{0}\"; filename=\"{1}\"\r\n Content-Type: application/octet-stream\r\n\r\n";

            //for (int i = 0; i < files.Length; i++)
            {

                //string header = string.Format(headerTemplate, "file" + i, files[i]);
                string header = string.Format(headerTemplate, "uplTheFile", file);//s[i]);

                byte[] headerbytes = System.Text.Encoding.UTF8.GetBytes(header);

                memStream.Write(headerbytes, 0, headerbytes.Length);


                FileStream fileStream = new FileStream(file, FileMode.Open,
                FileAccess.Read);
                byte[] buffer = new byte[1024];

                int bytesRead = 0;

                while ((bytesRead = fileStream.Read(buffer, 0, buffer.Length)) != 0)
                {
                    memStream.Write(buffer, 0, bytesRead);

                }


                memStream.Write(boundarybytes, 0, boundarybytes.Length);


                fileStream.Close();
            }

            httpWebRequest2.ContentLength = memStream.Length;

            Stream requestStream = httpWebRequest2.GetRequestStream();

            memStream.Position = 0;
            byte[] tempBuffer = new byte[memStream.Length];
            memStream.Read(tempBuffer, 0, tempBuffer.Length);
            memStream.Close();
            requestStream.Write(tempBuffer, 0, tempBuffer.Length);
            requestStream.Close();


            WebResponse webResponse2 = httpWebRequest2.GetResponse();

            Stream stream2 = webResponse2.GetResponseStream();
            StreamReader reader2 = new StreamReader(stream2);


            var result = reader2.ReadToEnd();

            webResponse2.Close();
            httpWebRequest2 = null;
            webResponse2 = null;
            return result;
        }
    }
}
