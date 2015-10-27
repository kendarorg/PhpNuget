<?php

define('__DOWNLOAD_DB__',Path::Combine(Settings::$DataRoot,"downloads"));

function incrementDownload($id,$version)
{
	$id = strtolower($id);
	$version = strtolower($version);
	if(!file_exists(__DOWNLOAD_DB__)){
		mkdir(__DOWNLOAD_DB__,0777,true);
	}
	$glob = Path::Combine(__DOWNLOAD_DB__,$id.".downloads");
	doIncrementDownload($glob);
	$glob = Path::Combine(__DOWNLOAD_DB__,$id.".".$version.".downloads");
	doIncrementDownload($glob);
}

function loadDownloadCount(&$nugetEntity)
{
	$id = strtolower($nugetEntity->Id);
	$version = strtolower($nugetEntity->Version);
	if(!file_exists(__DOWNLOAD_DB__)){
		$nugetEntity->DownloadCount = 0;
	}else{
		$glob = Path::Combine(__DOWNLOAD_DB__,$id.".downloads");
		$nugetEntity->DownloadCount = getIncrementDownload($glob);
		$glob = Path::Combine(__DOWNLOAD_DB__,$id.".".$version.".downloads");
		$nugetEntity->VersionDownloadCount = getIncrementDownload($glob);
	}
}

function getIncrementDownload($file)
{
	if(file_exists($file)){
		return intval(trim(file_get_contents($file)));
	}
	return 0;
}

function doIncrementDownload($file)
{
	if(!file_exists($file)){
		file_put_contents($file,1);
	}else{
		$data = intval(trim(file_get_contents($file)));
		$data++;
		file_put_contents($file,$data);
	}
}
?>