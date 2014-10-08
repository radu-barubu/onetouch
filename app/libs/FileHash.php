<?php

class FileHash
{
	public static $algorithm = "sha256";
	
	public static function getFileName($file)
	{
		$pos_slash = strrpos($file, "/") + 1;
		$pos_underscore = strpos(substr($file, $pos_slash), '_') + 1;
		
		return substr(substr($file, $pos_slash), $pos_underscore);
	}
	
	public static function getHash($file)
	{
		return hash_file(self::$algorithm, $file);
	}
}

?>