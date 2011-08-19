<?php
namespace Zoo;
define('ZOOCACHE_VER','1.0');
define('ZOOCACHE_INC', dirname(__FILE__));
include ZOOCACHE_INC.'/driver.php';
include ZOOCACHE_INC.'/cache.php';
include ZOOCACHE_INC.'/config.php';

class Api
{
	/**
	 * Deletes the whole cache
	 */
	public static function resetCache()
	{
		
	}
	
	/**
	 * Reset a cache stored under $key
	 */
	public static function reset($key)
	{
		// Load driver
		include ZOOCACHE_INC. '/drivers/' . Cache::option('driver') . '.php';
		
		if(Cache::$driver->getCache($key) !== FALSE)
		{
			// Reset Cache
			Cache::$driver->storeCache($key, "\0", 42, 42, 42);
		}
	}
}
?>