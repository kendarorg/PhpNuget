<?php 
define('__ROOT__', dirname(__FILE__));
require_once(__ROOT__.'/inc/virtualdirectory.php'); 
$virtualDirectory = new VirtualDirectory();
$baseUrl = $virtualDirectory->baseurl;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head id="Head1"><title>
	NuGet Private Repository
</title>
    <style>
        body { font-family: Calibri; }
    </style>
</head>
<body>
    <div>
        <h2>You are running NuGet.PhpServer v0.0.0.0</h2>
        <p>
            Click <a href="<?php echo $baseUrl;?>nuget/Packages">here</a> to view your packages.
        </p>
        <p>
            Click <a href="<?php echo $baseUrl;?>admin">here</a> to add/remove packages.
        </p>
        <fieldset style="width:800px">
            <legend><strong>Repository URLs</strong></legend>
            In the package manager settings, add the following URL to the list of 
            Package Sources:
            <blockquote>
                <strong><?php echo $baseUrl;?>nuget</strong>
            </blockquote>
            
            To enable pushing packages to this feed using the nuget command line tool (nuget.exe). Set the api key appSetting in web.config.
            
            <blockquote>
                <strong>nuget push {package file} -s <?php echo $baseUrl;?> {apikey}</strong>
            </blockquote>            
        </fieldset>

        
        <p style="font-size:1.1em">
            To add packages to the feed put package files (.nupkg files) in the folder "phpnuget\Packages".
        </p>
        
    </div>
</body>
</html>
