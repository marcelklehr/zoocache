<?php
namespace Zoo\drivers;
use \Zoo;

class File implements Zoo\Driver
{
	function install()
	{
		if(!is_dir($dir = Zoo\Cache::option('file.dir')))
			mkdir($dir);
	}
	
	function getCache($key)
	{		
		$file = Zoo\Cache::option('file.dir') . '/zoo.'.$key;
		
		// Open file
		if (($fp = @fopen($file, 'rb')) === FALSE)
		{
			Zoo\Cache::log('Couldn\'t open cache file');
			return FALSE;
		}
		
		// Get a shared lock
		flock($fp, LOCK_SH);
		
		Zoo\Cache::log('Reading cache file');
		
		$data = file_get_contents($file);

		// Release lock
		flock($fp, LOCK_UN);
		fclose($fp);
		
		$cache = unserialize($data);
		
		Zoo\Cache::log('Parsing cache data');
		
		if(!is_array($cache))
			return FALSE;
		
		return $cache;
	}
	
	function storeCache($key, $data, $timestamp, $size, $crc)
	{
		$cache = serialize(array('data'=>$data, 'timestamp'=>$timestamp, 'size'=>$size, 'crc'=>$crc));
		
		$file = Zoo\Cache::option('file.dir') . '/zoo.'.$key;
		unlink($file);
		
		$return = FALSE;
		// Lock file, ignore warnings as we might be creating this file
		$fpt = fopen($file, 'rb');
		flock($fpt, LOCK_EX);

		// php.net suggested I should use wb to make it work under Windows
		$fp=fopen($file, 'wb+');
		if(!$fp)
		{
			// Strange! We are not able to write the file!
			Zoo\Cache::log("Failed to open for write of $file");
		} else {
			fwrite($fp, $cache, strlen($cache));
			fclose($fp);
			$return = TRUE;
			
			Zoo\Cache::log("Wrote cache to file: $file");
		}

		// Release lock
		flock($fpt, LOCK_UN);
		fclose($fpt);
		
		// Return
		return $return;
	}
}

/**
 * Register Driver
 */
Zoo\Cache::$driver = new File;
?>