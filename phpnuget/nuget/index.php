<?php
define('__ROOT__',dirname( dirname(__FILE__)));
require_once(__ROOT__.'/inc/virtualdirectory.php'); 
$virtualDirectory = new VirtualDirectory();
$baseUrl = $virtualDirectory->baseurl;
$baseUrl = $virtualDirectory->upFromLevel($virtualDirectory->baseurl,1);
header("Content-Type: text/xml");
echo "<?xml version='1.0' encoding='utf-8' standalone='yes'?>\n";
?>
<service xml:base="<?php echo $baseUrl; ?>/nuget" 
    xmlns:atom="http://www.w3.org/2005/Atom" 
    xmlns:app="http://www.w3.org/2007/app" 
    xmlns="http://www.w3.org/2007/app">
  <workspace>
    <atom:title>Default</atom:title>
    <collection href="Packages">
      <atom:title>Packages</atom:title>
    </collection>
  </workspace>
</service>
