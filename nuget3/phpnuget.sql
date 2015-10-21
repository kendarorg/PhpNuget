-- phpMyAdmin SQL Dump
-- version 4.1.4
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2015 at 03:30 PM
-- Server version: 5.6.15-log
-- PHP Version: 5.5.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `phpnuget`
--

-- --------------------------------------------------------

--
-- Table structure for table `nugetdb_pkg`
--

CREATE TABLE IF NOT EXISTS `nugetdb_pkg` (
  `version` char(128) NOT NULL DEFAULT '',
  `title` text,
  `id` char(128) NOT NULL DEFAULT '',
  `author` text,
  `iconurl` text,
  `licenseurl` text,
  `projecturl` text,
  `downloadcount` int(11) DEFAULT NULL,
  `requirelicenseacceptance` tinyint(1) DEFAULT NULL,
  `description` text,
  `releasenotes` text,
  `published` text,
  `dependencies` text,
  `packagehash` text,
  `packagehashalgorithm` text,
  `packagesize` int(11) DEFAULT NULL,
  `copyright` text,
  `tags` text,
  `isabsolutelatestversion` tinyint(1) DEFAULT NULL,
  `islatestversion` tinyint(1) DEFAULT NULL,
  `listed` tinyint(1) DEFAULT NULL,
  `versiondownloadcount` int(11) DEFAULT NULL,
  `references` text,
  `targetframework` text,
  `summary` text,
  `isprerelease` tinyint(1) DEFAULT NULL,
  `owners` text,
  `userid` text,
  PRIMARY KEY (`version`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `nugetdb_pkg`
--

INSERT INTO `nugetdb_pkg` (`version`, `title`, `id`, `author`, `iconurl`, `licenseurl`, `projecturl`, `downloadcount`, `requirelicenseacceptance`, `description`, `releasenotes`, `published`, `dependencies`, `packagehash`, `packagehashalgorithm`, `packagesize`, `copyright`, `tags`, `isabsolutelatestversion`, `islatestversion`, `listed`, `versiondownloadcount`, `references`, `targetframework`, `summary`, `isprerelease`, `owners`, `userid`) VALUES
('4.0.30506.0', 'Microsoft ASP.NET MVC 4', 'Microsoft.AspNet.Mvc', 's:9:"Microsoft";', 'http://localhost:8020/pnm/content/packagedefaulticon-50x50.png', 'http://www.microsoft.com/web/webpi/eula/mvc_4_eula_enu.htm', 'http://www.asp.net/mvc', NULL, 1, 'This package contains the runtime assemblies for ASP.NET MVC. ASP.NET MVC gives you a powerful, patterns-based way to build dynamic websites that enables a clean separation of concerns and that gives you full control over markup.', NULL, '2015-10-21T14:32:08.000000Z', 'a:2:{i:0;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:25:"Microsoft.AspNet.WebPages";s:7:"Version";s:11:"2.0.20710.0";}i:1;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:22:"Microsoft.AspNet.Razor";s:7:"Version";s:11:"2.0.20710.0";}}', 'zuQ/kRdHvVYb8oB3TwgMm7VHJ6FPAM7CizQ1FJ9vuHI1uaAMHKfKWJ/GvC8AhYPJuK+i8Q6RZnxic4BnWik18A==', 'SHA512', 266592, 'Microsoft', 'Microsoft AspNet Mvc AspNetMvc', NULL, NULL, 1, NULL, 'a:0:{}', NULL, NULL, 0, 'Microsoft', '{372F9D09-5B4B-4D68-A5FB-AB797193FC25}'),
('4.0.40804.0', 'Microsoft ASP.NET MVC 4', 'Microsoft.AspNet.Mvc', 's:9:"Microsoft";', 'http://localhost:8020/pnm/content/packagedefaulticon-50x50.png', 'http://www.microsoft.com/web/webpi/eula/mvc_4_eula_enu.htm', 'http://www.asp.net/mvc', NULL, 1, 'This package contains the runtime assemblies for ASP.NET MVC. ASP.NET MVC gives you a powerful, patterns-based way to build dynamic websites that enables a clean separation of concerns and that gives you full control over markup.', NULL, '2015-10-21T14:32:17.000000Z', 'a:2:{i:0;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:25:"Microsoft.AspNet.WebPages";s:7:"Version";s:11:"2.0.20710.0";}i:1;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:22:"Microsoft.AspNet.Razor";s:7:"Version";s:11:"2.0.20710.0";}}', 'tQrw7I+Ol1Kr3kDKcA0XNT68ctjciLIUZqGtMBk7fNjEGUUVDifwlOJJXUBQxUayv4aHwB+OOiPFF3ZD6VXmRw==', 'SHA512', 267029, 'Microsoft', 'Microsoft AspNet Mvc AspNetMvc', NULL, NULL, 1, NULL, 'a:0:{}', NULL, NULL, 0, 'Microsoft', '{372F9D09-5B4B-4D68-A5FB-AB797193FC25}'),
('1.0.0.0', 'DigitalMedia.GUIShell.WebMatrix.SDK', 'DigitalMedia.GUIShell.WebMatrix.SDK', 's:28:"Deltatre WebPLU-DigitalMedia";', 'http://localhost:8020/pnm/content/packagedefaulticon-50x50.png', NULL, NULL, NULL, 1, 'DigitalMedia.GUIShell.SDK for .NET Framework 4.5', NULL, '2015-10-21T15:27:51.000000Z', 'a:10:{i:0;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:25:"DigitalMedia.GUIShell.SDK";s:7:"Version";s:7:"0.0.0.0";}i:1;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:16:"Kendar.Linq2Rest";s:7:"Version";s:7:"4.1.0.2";}i:2;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:20:"Microsoft.AspNet.Mvc";s:7:"Version";s:11:"4.0.40804.0";}i:3;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:22:"Microsoft.AspNet.Razor";s:7:"Version";s:5:"3.2.3";}i:4;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:25:"Microsoft.AspNet.WebPages";s:7:"Version";s:5:"3.2.3";}i:5;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:30:"Microsoft.AspNet.WebPages.Data";s:7:"Version";s:5:"3.2.3";}i:6;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:33:"Microsoft.AspNet.WebPages.WebData";s:7:"Version";s:5:"3.2.3";}i:7;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:28:"Microsoft.Web.Infrastructure";s:7:"Version";s:7:"1.0.0.0";}i:8;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:15:"Newtonsoft.Json";s:7:"Version";s:5:"6.0.8";}i:9;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:35:"Deltatre.WebPLU.Infrastructure.Core";s:7:"Version";s:6:"1.3.10";}}', 'GtNLapmVL3HomVDpnFjRGi4IvOay8lDnkwfzLdaqILitCWscUc7wEJx7SP/v2QbKlArnbPRrRBNtsjnA/ScOow==', 'SHA512', 25717, 'Deltatre WebPLU-DigitalMedia', NULL, NULL, NULL, 1, NULL, 'a:0:{}', NULL, NULL, 0, 'Deltatre WebPLU-DigitalMedia', '{372F9D09-5B4B-4D68-A5FB-AB797193FC25}'),
('1.0.0.1', 'DigitalMedia.GUIShell.WebMatrix.SDK', 'DigitalMedia.GUIShell.WebMatrix.SDK', 's:28:"Deltatre WebPLU-DigitalMedia";', 'http://localhost:8020/pnm/content/packagedefaulticon-50x50.png', NULL, NULL, NULL, 1, 'DigitalMedia.GUIShell.SDK for .NET Framework 4.5', NULL, '2015-10-21T15:27:57.000000Z', 'a:10:{i:0;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:25:"DigitalMedia.GUIShell.SDK";s:7:"Version";s:7:"0.0.0.0";}i:1;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:16:"Kendar.Linq2Rest";s:7:"Version";s:7:"4.1.0.2";}i:2;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:20:"Microsoft.AspNet.Mvc";s:7:"Version";s:11:"4.0.40804.0";}i:3;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:22:"Microsoft.AspNet.Razor";s:7:"Version";s:5:"3.2.3";}i:4;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:25:"Microsoft.AspNet.WebPages";s:7:"Version";s:5:"3.2.3";}i:5;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:30:"Microsoft.AspNet.WebPages.Data";s:7:"Version";s:5:"3.2.3";}i:6;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:33:"Microsoft.AspNet.WebPages.WebData";s:7:"Version";s:5:"3.2.3";}i:7;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:28:"Microsoft.Web.Infrastructure";s:7:"Version";s:7:"1.0.0.0";}i:8;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:15:"Newtonsoft.Json";s:7:"Version";s:5:"6.0.8";}i:9;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:35:"Deltatre.WebPLU.Infrastructure.Core";s:7:"Version";s:6:"1.3.10";}}', 'zOVyu0WszXRzUQYUNDaGYe7meu1RRfDGrH3HRFIsKGXt08MSYeiN4PMKnGN4OfJh1qleBEGyByiAMh7miaulbw==', 'SHA512', 25761, 'Deltatre WebPLU-DigitalMedia', NULL, NULL, NULL, 1, NULL, 'a:0:{}', NULL, NULL, 0, 'Deltatre WebPLU-DigitalMedia', '{372F9D09-5B4B-4D68-A5FB-AB797193FC25}'),
('1.0.0.1-alphaC510121', 'DigitalMedia.GUIShell.WebMatrix.SDK', 'DigitalMedia.GUIShell.WebMatrix.SDK', 's:28:"Deltatre WebPLU-DigitalMedia";', 'http://localhost:8020/pnm/content/packagedefaulticon-50x50.png', NULL, NULL, NULL, 1, 'DigitalMedia.GUIShell.SDK for .NET Framework 4.5', NULL, '2015-10-21T15:28:03.000000Z', 'a:10:{i:0;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:25:"DigitalMedia.GUIShell.SDK";s:7:"Version";s:7:"0.0.0.0";}i:1;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:16:"Kendar.Linq2Rest";s:7:"Version";s:7:"4.1.0.2";}i:2;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:20:"Microsoft.AspNet.Mvc";s:7:"Version";s:11:"4.0.40804.0";}i:3;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:22:"Microsoft.AspNet.Razor";s:7:"Version";s:5:"3.2.3";}i:4;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:25:"Microsoft.AspNet.WebPages";s:7:"Version";s:5:"3.2.3";}i:5;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:30:"Microsoft.AspNet.WebPages.Data";s:7:"Version";s:5:"3.2.3";}i:6;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:33:"Microsoft.AspNet.WebPages.WebData";s:7:"Version";s:5:"3.2.3";}i:7;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:28:"Microsoft.Web.Infrastructure";s:7:"Version";s:7:"1.0.0.0";}i:8;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:15:"Newtonsoft.Json";s:7:"Version";s:5:"6.0.8";}i:9;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:35:"Deltatre.WebPLU.Infrastructure.Core";s:7:"Version";s:6:"1.3.10";}}', '8CiQrS/KOj9wJwv7bZwNuoEIFeIfWPBycygnD0GH7woOKr3csO2hRqGO9jlhMJhjuTIZtvEwSZn87zDWvQvpiA==', 'SHA512', 25738, 'Deltatre WebPLU-DigitalMedia', NULL, NULL, NULL, 1, NULL, 'a:0:{}', NULL, NULL, 1, 'Deltatre WebPLU-DigitalMedia', '{372F9D09-5B4B-4D68-A5FB-AB797193FC25}'),
('1.0.0.1-alphaC510157', 'DigitalMedia.GUIShell.WebMatrix.SDK', 'DigitalMedia.GUIShell.WebMatrix.SDK', 's:28:"Deltatre WebPLU-DigitalMedia";', 'http://localhost:8020/pnm/content/packagedefaulticon-50x50.png', NULL, NULL, NULL, 1, 'DigitalMedia.GUIShell.SDK for .NET Framework 4.5', NULL, '2015-10-21T15:28:10.000000Z', 'a:10:{i:0;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:25:"DigitalMedia.GUIShell.SDK";s:7:"Version";s:7:"0.0.0.0";}i:1;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:16:"Kendar.Linq2Rest";s:7:"Version";s:7:"4.1.0.2";}i:2;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:20:"Microsoft.AspNet.Mvc";s:7:"Version";s:11:"4.0.40804.0";}i:3;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:22:"Microsoft.AspNet.Razor";s:7:"Version";s:5:"3.2.3";}i:4;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:25:"Microsoft.AspNet.WebPages";s:7:"Version";s:5:"3.2.3";}i:5;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:30:"Microsoft.AspNet.WebPages.Data";s:7:"Version";s:5:"3.2.3";}i:6;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:33:"Microsoft.AspNet.WebPages.WebData";s:7:"Version";s:5:"3.2.3";}i:7;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:28:"Microsoft.Web.Infrastructure";s:7:"Version";s:7:"1.0.0.0";}i:8;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:15:"Newtonsoft.Json";s:7:"Version";s:5:"6.0.8";}i:9;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:35:"Deltatre.WebPLU.Infrastructure.Core";s:7:"Version";s:6:"1.3.10";}}', '+IlWp7E+k3ocipZzLJVY9O3ubrt+0BSVcgaV67cFRPIYTkdL85VCiR5s/kwzXSIO9ySS2LGVrIlHWLLk5xgSCg==', 'SHA512', 25787, 'Deltatre WebPLU-DigitalMedia', NULL, NULL, NULL, 1, NULL, 'a:0:{}', NULL, NULL, 1, 'Deltatre WebPLU-DigitalMedia', '{372F9D09-5B4B-4D68-A5FB-AB797193FC25}'),
('1.0.0.3', 'DigitalMedia.GUIShell.WebMatrix.SDK', 'DigitalMedia.GUIShell.WebMatrix.SDK', 's:28:"Deltatre WebPLU-DigitalMedia";', 'http://localhost:8020/pnm/content/packagedefaulticon-50x50.png', NULL, NULL, NULL, 1, 'DigitalMedia.GUIShell.SDK for .NET Framework 4.5', NULL, '2015-10-21T15:28:20.000000Z', 'a:10:{i:0;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:25:"DigitalMedia.GUIShell.SDK";s:7:"Version";s:7:"0.0.0.0";}i:1;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:16:"Kendar.Linq2Rest";s:7:"Version";s:7:"4.1.0.2";}i:2;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:20:"Microsoft.AspNet.Mvc";s:7:"Version";s:11:"4.0.40804.0";}i:3;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:22:"Microsoft.AspNet.Razor";s:7:"Version";s:5:"3.2.3";}i:4;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:25:"Microsoft.AspNet.WebPages";s:7:"Version";s:5:"3.2.3";}i:5;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:30:"Microsoft.AspNet.WebPages.Data";s:7:"Version";s:5:"3.2.3";}i:6;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:33:"Microsoft.AspNet.WebPages.WebData";s:7:"Version";s:5:"3.2.3";}i:7;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:28:"Microsoft.Web.Infrastructure";s:7:"Version";s:7:"1.0.0.0";}i:8;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:15:"Newtonsoft.Json";s:7:"Version";s:5:"6.0.8";}i:9;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:35:"Deltatre.WebPLU.Infrastructure.Core";s:7:"Version";s:6:"1.3.10";}}', '5Qpi2MiJvFP1r8XcwFHBhqV7vyecba3WTw3AZ2+n2S2cLA4HbSbo/NnwtvaWWh9PbPwu+LCD9ng72QMAwFHFlw==', 'SHA512', 25765, 'Deltatre WebPLU-DigitalMedia', NULL, NULL, NULL, 1, NULL, 'a:0:{}', NULL, NULL, 0, 'Deltatre WebPLU-DigitalMedia', '{372F9D09-5B4B-4D68-A5FB-AB797193FC25}'),
('1.0.0.3-alphaC511673', 'DigitalMedia.GUIShell.WebMatrix.SDK', 'DigitalMedia.GUIShell.WebMatrix.SDK', 's:28:"Deltatre WebPLU-DigitalMedia";', 'http://localhost:8020/pnm/content/packagedefaulticon-50x50.png', NULL, NULL, NULL, 1, 'DigitalMedia.GUIShell.SDK for .NET Framework 4.5', NULL, '2015-10-21T15:28:27.000000Z', 'a:10:{i:0;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:25:"DigitalMedia.GUIShell.SDK";s:7:"Version";s:7:"0.0.0.0";}i:1;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:16:"Kendar.Linq2Rest";s:7:"Version";s:7:"4.1.0.2";}i:2;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:20:"Microsoft.AspNet.Mvc";s:7:"Version";s:11:"4.0.40804.0";}i:3;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:22:"Microsoft.AspNet.Razor";s:7:"Version";s:5:"3.2.3";}i:4;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:25:"Microsoft.AspNet.WebPages";s:7:"Version";s:5:"3.2.3";}i:5;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:30:"Microsoft.AspNet.WebPages.Data";s:7:"Version";s:5:"3.2.3";}i:6;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:33:"Microsoft.AspNet.WebPages.WebData";s:7:"Version";s:5:"3.2.3";}i:7;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:28:"Microsoft.Web.Infrastructure";s:7:"Version";s:7:"1.0.0.0";}i:8;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:15:"Newtonsoft.Json";s:7:"Version";s:5:"6.0.8";}i:9;O:15:"NugetDependency":3:{s:7:"IsGroup";b:0;s:2:"Id";s:35:"Deltatre.WebPLU.Infrastructure.Core";s:7:"Version";s:6:"1.3.10";}}', 'HVOeOF3uCSh0wjFpTyvyKgye6aV2gu5FN6tnTcPtRh4+DHv6m8cSMBEo3gi3CkEJtR6m2h6gMimBKxj4kJ4bFQ==', 'SHA512', 25954, 'Deltatre WebPLU-DigitalMedia', NULL, NULL, NULL, 1, NULL, 'a:0:{}', NULL, NULL, 1, 'Deltatre WebPLU-DigitalMedia', '{372F9D09-5B4B-4D68-A5FB-AB797193FC25}');

-- --------------------------------------------------------

--
-- Table structure for table `nugetdb_usrs`
--

CREATE TABLE IF NOT EXISTS `nugetdb_usrs` (
  `userid` char(128) NOT NULL DEFAULT '',
  `name` text,
  `company` text,
  `md5password` text,
  `packages` text,
  `enabled` tinyint(1) DEFAULT NULL,
  `email` text,
  `token` text,
  `admin` tinyint(1) DEFAULT NULL,
  `id` text,
  PRIMARY KEY (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `nugetdb_usrs`
--

INSERT INTO `nugetdb_usrs` (`userid`, `name`, `company`, `md5password`, `packages`, `enabled`, `email`, `token`, `admin`, `id`) VALUES
('admin', 'Administrator', '', '5f4dcc3b5aa765d61d8327deb882cf99', NULL, 1, 'nuget@localhost', '{2A2A0944-3AB6-4629-61EE-BB7E0F62FF04}', 1, '{372F9D09-5B4B-4D68-A5FB-AB797193FC25}');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
