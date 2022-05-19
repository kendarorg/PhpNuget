<?php
class Path
{
	public static function Combine($root,$path)
	{
		$root = rtrim($root,"\\/");
		$path = ltrim($path,"\\/");
		if(strlen($root)==0) return $path;
		if(strlen($path)==0) return $root;
		return $root.DIRECTORY_SEPARATOR.$path;
	}
	
	public static function CleanUp($path)
	{
		$path = str_replace("\\",DIRECTORY_SEPARATOR,$path);
		$path = str_replace("/",DIRECTORY_SEPARATOR,$path);
		return rtrim($path,"\\/");
	}
}

?>