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
namespace Zoo;

class Cache
{
    const VERSION = '0.6.0';
    
	public $key;
	
	public static $driver;
	public static $filters;
	
	/**
	 * Construct entity properties
	 */
	private function __construct($key)
	{
		$this->key = $key;
	}
    
    /**
     * Load everythin that's needed to work
     */
    public static function setUp()
    {
        // Load config
        $config = dirname(__FILE__). '/../../config.php';
        if(!file_exists($config)) throw new Exception('Zoocache config file not found (should be at "'.$config.'")');
        include $config;
        
        // Load driver
        if(!isset(self::$driver))
        {
            $driver_name = Config::get('driver');
            $class = '\\Zoo\\Drivers\\'.$driver_name;
            self::$driver = new $class;
            if(!(self::$driver instanceof Driver)) throw new Exception('Registered Zoocache driver '.Config::get('driver').' must implement interface Zoo\Driver.');
        }
    }
    
    /**
	 * Access a cache entry
	 */
	public static function item($key)
	{
        self::setUp();
        return new Cache($key);
	}
    
    /**
	 * Cache::retrieve returns the cached item if it's not expired, otherwise it'll execute the callback and store and pass through its return value.
	 */
	public static function retrieve($key, $callback, $expiresIn)
	{
        $item = Cache::item($key);
        if(($data = $item->get()) === FALSE) {
            $data = $callback();
            $item->store($data, $expiresIn);
        }
        return $data;
	}
    
    /**
	 * Deletes the whole cache, and returns FALSE on error.
	 */
	public static function resetAll()
	{
        self::setUp();
		return (self::$driver->reset() !== FALSE);
	}
	
//:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

	/**
	 * Calls the driver for restoring the cached content.
	 */
	function get()
	{
		$c = self::$driver->get($this->key);
		
		if($c === FALSE)
		{
			self::log('Couldn\'t get cache data');
			return false;
		}
		
		return $c;
	}
	
	/**
	 * Calls the driver for storing a new snapshot.
	 */
	function store($content, $expires)
	{
		self::log('Storing new cache');
		return self::$driver->store($this->key, $expires+time(), $content);
	}
	
	/**
	 * Reset the snapshot stored under $cache->key
	 */
	public function reset()
	{
		// Store nothing for the current key, but make shure it will be determined as invalid
		return self::$driver->store($this->key, $timeout=time()-5, '');
	}
	
//:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

	/**
	 * Outputs every log statement to the HTTP header when in debug mode
	 */
	static function log($string)
	{
		static $c = 0;
		if(Config::get('debug'))
		{
			header("X-Zoocache-Log-$c: ".$string, true);
			$c++;
		}
	}
}
?>