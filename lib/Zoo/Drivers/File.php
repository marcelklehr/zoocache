<?php
/**
 * Zoocache - Intelligent output caching
 * Copyright (c) 2011-2012 by Marcel Klehr <mklehr@gmx.net>
 * 
 * MIT LICENSE
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package Zoocache
 * @author Marcel Klehr <mklehr@gmx.net>
 * @copyright (c) 2011-2012, Marcel Klehr
 */
namespace Zoo\Drivers;
use \Zoo;

class File implements Zoo\Driver
{
	function install()
	{
		if(!is_dir($dir = Zoo\Config::get('file.dir')))
			mkdir($dir);
		return;
	}
	
	function get($key)
	{		
		$file = Zoo\Config::get('file.dir') . '/zoo.'.$key;
		
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
	
	function store($key, $data, $timestamp, $size, $crc)
	{
		$cache = serialize(array('data'=>$data, 'timestamp'=>$timestamp, 'size'=>$size, 'crc'=>$crc));
		
		$file = Zoo\Config::get('file.dir') . '/zoo.'.$key;
		if(file_exists($file))
			unlink($file);
		
		$return = FALSE;
		// Lock file, ignore warnings as we might be creating this file
		$fpt = @fopen($file, 'rb');
		@flock($fpt, LOCK_EX);

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
		@flock($fpt, LOCK_UN);
		@fclose($fpt);
		
		// Return
		return $return;
	}
	
	function reset()
	{
		self::emptyDir( Zoo\Config::get('file.dir') );
	}
	
	static function emptyDir($directory)
	{
		if(substr($directory,-1) == "/") {
			$directory = substr($directory,0,-1);
		}

		if(!file_exists($directory) || !is_dir($directory)) {
			return false;
		} elseif(!is_readable($directory)) {
			return false;
		} else {
			$directoryHandle = opendir($directory);
		   
			while ($contents = readdir($directoryHandle)) {
				if($contents != '.' && $contents != '..') {
					$path = $directory . "/" . $contents;
				   
					if(is_dir($path)) {
						deleteAll($path);
					} else {
						unlink($path);
					}
				}
			}
		   
			closedir($directoryHandle);
		   
			return true;
		}
	}
}
?>