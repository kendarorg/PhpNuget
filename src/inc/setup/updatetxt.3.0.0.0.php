<?php


function splitVersion($ov){
	$v = unserialize($ov);
	$blocks= explode("-",$v);
	$beta = sizeof($blocks)>=2?join("-",array_slice($blocks,1)):"";
	$number = explode(".",$blocks[0]);
	
	while(sizeof($number)<4){
		array_insert($number,"0",0);
	}
	
	$newData = array();
	$newData[] = serialize($number[0]);
	$newData[] = serialize($number[1]);
	$newData[] = serialize($number[2]);
	$newData[] =serialize($number[3]);
	$newData[] = serialize($beta);
	$newData[] = $ov;
	return $newData;
}

function updateTo3000($dbFile){
$newVersion = "@Version:4.0.0.0";


if($dbFile=="nugetdb_usrs.txt"){
	$fp = fopen(__DATABASE_DIR__."/".$dbFile,'r');
	$content = fread($fp,filesize(__DATABASE_DIR__."/".$dbFile));
	fclose($fp);
	file_put_contents(__DATABASE_DIR__."/".$dbFile.".3.0.0.0",$content);
	$splitted = explode("\n",$content);
	$splitted[0]=$newVersion;

	$rc =  join("\n",$splitted);
	file_put_contents(__DATABASE_DIR__."/".$dbFile,$rc);

	echo "<li>Db ".$dbFile." udpated to ".$newVersion."</li>";
	return "4.0.0.0";
}else if($dbFile=="nugetdb_pkg.txt"){

$newHeader = "Version0:|:Version1:|:Version2:|:Version3:|:VersionBeta:|:Version:|:Title:|:Id:|:Author:|:IconUrl:|:LicenseUrl:|:ProjectUrl:|:DownloadCount:|:RequireLicenseAcceptance:|:Description:|:ReleaseNotes:|:Published:|:Dependencies:|:PackageHash:|:PackageHashAlgorithm:|:PackageSize:|:Copyright:|:Tags:|:IsAbsoluteLatestVersion:|:IsLatestVersion:|:Listed:|:VersionDownloadCount:|:References:|:TargetFramework:|:Summary:|:IsPreRelease:|:Owners:|:UserId";

$fp = fopen(__DATABASE_DIR__."/".$dbFile,'r');
$content = fread($fp,filesize(__DATABASE_DIR__."/".$dbFile));
fclose($fp);
file_put_contents(__DATABASE_DIR__."/".$dbFile.".3.0.0.0",$content);
$splitted = explode("\n",$content);

$newco = array();
$newco[] = $newVersion;
$newco[] = $newHeader;


for($i=2;$i<sizeof($splitted);$i++){
	$row = $splitted[$i];
	$data = explode(":|:",$row);
	
	$newData = array();
	
	$version = splitVersion($data[0]);
	for($j=0;$j<sizeof($version);$j++){
		$newData[] = $version[$j];
	}
	/**/
	for($j=1;$j<sizeof($data);$j++){
		$newData[]= $data[$j];
	}
	$newco[] = join(":|:",$newData);
}
$rc =  join("\n",$newco);
file_put_contents(__DATABASE_DIR__."/".$dbFile,$rc);

echo "<li>Db ".$dbFile." udpated to ".$newVersion."</li>";

	return "4.0.0.0";
}
}
?>