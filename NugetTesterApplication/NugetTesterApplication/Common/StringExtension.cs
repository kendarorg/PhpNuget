using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Xml;

namespace NugetTesterApplication.Common
{
    public static class StringExtension
    {
        public static XmlDocument ToXml(this string toParse)
        {
            XmlDocument xml = new XmlDocument();
            xml.LoadXml(toParse);
            return xml;
        }

        public static IEnumerable<XmlNode> FindXmlNodes(this XmlDocument xml, params string[] lst)
        {
            if (lst.Length == 0) yield break;
            var tag = lst.First().ToLowerInvariant();
            foreach (XmlNode node in xml.ChildNodes)
            {
                if (node.Name.ToLowerInvariant() == tag)
                {
                    if (lst.Length == 1)
                    {
                        yield return node;
                    }
                    else if (lst.Length > 1)
                    {
                        foreach (var item in node.FindXmlNodes(lst.Skip(1).ToArray()))
                        {
                            yield return item;
                        }
                    }
                }

            }
        }

        public static IEnumerable<XmlNode> FindXmlNodes(this XmlNode xml, params string[] lst)
        {

            if (lst.Length == 0) yield break;
            var tag = lst.First().ToLowerInvariant();
            foreach (XmlNode node in xml.ChildNodes)
            {
                if (node.Name.ToLowerInvariant() == tag)
                {
                    if (lst.Length == 1)
                    {
                        yield return node;
                    }
                    else if (lst.Length > 1)
                    {
                        foreach (var item in node.FindXmlNodes(lst.Skip(1).ToArray()))
                        {
                            yield return item;
                        }
                    }
                }
            }
        }
    }
}
