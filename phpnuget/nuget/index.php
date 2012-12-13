<?php
require_once("../inc/virtualdirectory.php");
var $virtualDirectory = new VirtualDirectory();
var $baseUrl = $virtualDirectory->baseurl;
$baseUrl = $virtualDirectory->upFromLevel($baseUrl,1);
header("Content-Type: text/xml");
?>
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<service xml:base="<?php echo $baseUrl;?>/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app" xmlns="http://www.w3.org/2007/app">
  <workspace>
    <atom:title>Default</atom:title>
    <collection href="Packages">
      <atom:title>Packages</atom:title>
    </collection>
  </workspace>
</service>