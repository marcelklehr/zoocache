<?php
namespace Zoo;

/**
 * Flags for createKey()
 */
define('KEY_DOMAIN', 8);  //1000
define('KEY_GETVARS', 4); //0100
define('KEY_SCHEME', 2);  //0010

class Cache
{
	public $key;
	public $url;
	
	public static $driver;
	
	/**
	 * Init Cache - load driver
	 */
	public static function init($url=null)
	{
		// Load driver
        if(!file_exists($driver = ZOOCACHE_INC. '/drivers/' . Config::get('driver') . '.php')) throw new Exception('Zoocache driver not found (should be in "drivers/'.Config::get('driver').'.php")');
		if(!isset(self::$driver)) include $driver;
		
		if(!(self::$driver instanceof Zoo\Driver))
			throw new Exception('Registered Zoocache driver must be an implementation of interface Zoo\Driver.');
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
	
//:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

	/**
	 * Calls the driver for restoring the cached content.
	 */
	function getCache()
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
	function storeCache($content)
	{
		self::log('Storing new cache');
		$c['data'] = $content;
		$c['timestamp'] = time();
		$c['size'] = strlen($content);
		$c['crc'] = crc32($content);
		
		return self::$driver->store($this->key, $c['data'], $c['timestamp'], $c['size'], $c['crc']);
	}
	
	/**
	 * Reset the snapshot stored under $key
	 */
	public function resetCache()
	{
		// Store nothing for the current key, but make shure it will be determined as invalid
		return self::$driver->store($this->key, '', 42, 0, crc32(''));
	}
	
	/**
	 * Deletes the whole cache, and returns FALSE on error.
	 */
	public function reset()
	{
		return (self::$driver->resetCache() !== FALSE);
	}
	
//:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
	
	/**
	 * Creates storage key with the options
	 */
	static function createKey($url)
	{
	  /* Generate Script identification string */
		$flags = Config::get('keygeneration');
		
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