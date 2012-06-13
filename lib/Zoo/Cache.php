<?php
/**
 *  Zoocache - Intelligent, neatly integrating, easily extenable output caching tool
 *  Copyright (C) 2011  Marcel Klehr <marcel.klehr@gmx.de>
 *
 *  This program is free software; you can redistribute it and/or modify it under the 
 *  terms of the GNU General Public License as published by the Free Software Foundation;
 *  either version 3 of the License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *  without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *  See the GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License along with this program;
 *  if not, see <http://www.gnu.org/licenses/>.
 *
 * @package Zoocache
 * @author Marcel Klehr <mklehr@gmx.net>
 * @copyright (c) 2011, Marcel Klehr
 */
namespace Zoo;

/**
 * Flags for createKey()
 */
define('KEY_DOMAIN', 8);  //1000
define('KEY_GETVARS', 4); //0100
define('KEY_SCHEME', 2);  //0010

class Cache
{
    const VERSION = '0.5.0';
    
	public $key;
	public $url;
	
	public static $driver;
	public static $filters;
	
	/**
	 * Init Cache - load driver
	 */
	public static function init($url=null)
	{
        self::setUp();
		
		if(!(self::$driver instanceof Driver)) throw new Exception('Registered Zoocache driver '.Config::get('driver').' must implement interface Zoo\Driver.');
        return new Cache($url);
	}
	
	/**
	 * Construct entity properties
	 */
	private function __construct($url=null)
	{
		$this->url = $url;
		if($url === null)
		{
			$this->url = (@$_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
			$this->url .= $_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];
			$this->url.= (!empty($_SERVER['QUERY_STRING']))
							? '?'.$_SERVER['QUERY_STRING']
							: '';
		}
		
		$this->key = self::createKey($this->url);
	}
    
    public static function setUp()
    {
        // Load config
        $config = dirname(__FILE__). '/../../config.php';
        if(!file_exists($config)) throw new Exception('Zoocache config file not found (should be at "'.$config.'")');
        include $config;
        
        // Load driver
        if(!isset(self::$driver))
        {
            $driver_name = ucwords(Config::get('driver'));
            $class = '\\Zoo\\Drivers\\'.$driver_name;
            self::$driver = new $class;
        }
        
        // Load filters
        if(!isset(self::$filters))
        {
            self::$filters = array();
            $filters = Config::get('filters');
            foreach($filters as $filter)
            {
                $path = dirname(__FILE__). '/Filters/' . ucwords($filter) . '.php';
                if(!file_exists($path)) throw new Exception('Zoocache filter not found (should be at "'.$path.'")');
                include $path;
            }
        }
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
		
		return array(
			'data' => $c['data'],
			'size' => $c['size'],
			'crc' => $c['crc'],
			'timestamp' => $c['timestamp']
		);
	}
	
	/**
	 * Calls the driver for storing a new snapshot.
	 */
	function store($content)
	{
		self::log('Storing new cache');
		$c['data'] = $content;
		$c['timestamp'] = time();
		$c['size'] = strlen($content);
		$c['crc'] = crc32($content);
		
		return self::$driver->store($this->key, $c['data'], $c['timestamp'], $c['size'], $c['crc']);
	}
	
	/**
	 * Reset the snapshot stored under $cache->key
	 */
	public function reset()
	{
		// Store nothing for the current key, but make shure it will be determined as invalid
		return self::$driver->store($this->key, '', 42, 0, crc32(''));
	}
	
	/**
	 * Deletes the whole cache, and returns FALSE on error.
	 */
	public function resetAll()
	{
		return (self::$driver->reset() !== FALSE);
	}
	
//:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    
    /**
	 * Registers a filter
	 */
	static function applyFilter($func)
	{
		if(!is_callable($func)) return false;
        self::$filters[] = $func;
	}
    
    
    /**
	 * Applies all registered output filters
	 */
    static function filter($buffer)
    {
        foreach(self::$filters as $filter)
        {
            $b = $filter($buffer);
            if($b !== FALSE) $buffer = $b;
        }
        return $buffer;
    }
	
	/**
	 * Creates storage key with the options
	 */
	static function createKey($url)
	{
	  /* Generate Script identification string */
		$flags = Config::get('keygenerator');
		
		// call user-defined function
		if(is_callable($flags)) {
			return md5(flags($url));
		}
		
		$url = parse_url($url);
		
		$key = $url['path'];
		
		// check DOMAIN flag
		if((KEY_DOMAIN & $flags) == KEY_DOMAIN)
		{
			$key = $url['host'].$key;
		}
		
		// check GETVARS flag
		if((KEY_GETVARS & $flags) == KEY_GETVARS)
		{
			$key .= '?';
			$key .= (isset($url['query'])) ? $url['query'] : '';
		}
		
		// check SCHEME flag
		if((KEY_SCHEME & $flags) == KEY_SCHEME)
		{
			$key = $url['scheme'].'://'.$key;
		}
		
		return md5($key);
	}

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