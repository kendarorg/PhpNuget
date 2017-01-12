<?php
require_once(__ROOT__."/inc/phpnugetobjectsearch.php");
$dbfactory = "newSmallTxtDb";

$db = new NuGetDb();
$allPackages= $db->Query();

$db = new UserDb();
$allUsers= $db->Query();

$dbfactory = "newMySqlDb";

$db = new NuGetDb();
foreach($allPackages as $package){
	$db->AddRow($package,false);
}
$db = new UserDb();
foreach($allUsers as $user){
	$db->AddRow($user,false);
}

?>