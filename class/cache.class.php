<?php
namespace Zoo;

/**
 * Flags for createKey()
 */
define('KEY_DOMAIN', 8);  //1000
define('KEY_GETVARS', 4); //0100
define('KEY_SCHEME', 2); //0010

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
		include ZOOCACHE_INC. '/drivers/' . Config::get('driver') . '.php';
		
		try
		{
			return new Cache($url, self::$driver);
		}catch(Exception $e)
		{
			throw new DomainException('Registered Zoocache driver must be an implementation of interface Zoo\Driver.', 0, $e);
		}
	}
	
	/**
	 * Construct entity properties
	 */
	private function __construct($url=null, Driver $d)
	{
		$this->url = $url;
		if($url === null)
		{
			$this->url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];
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
		$c['data'] = (Config::get('gzip'))
							? gzcompress($content)
							: $content;
		$c['timestamp'] = time();
		$c['size'] = strlen($content);
		$c['crc'] = crc32($content);
		
		return self::$driver->store($this->key, $c['data'], $c['timestamp'], $c['size'], $c['crc']);
	}
	
	/**
	 * Reset a snapshot stored under $key
	 */
	public function recache()
	{
		self::log('Calling monkey for recaching');
		
		$fp = fsockopen($_SERVER['SERVER_NAME'], 80);
		if (!$fp) {
			return FALSE;
		}
		
		$monkey = dirname($_SERVER['SCRIPT_NAME']).'/monkey.php?recache';
		$post = '{"resource":"'.$this->url.'", "key":"'.$this->key.'"}';
		$http = "GET ".$monkey." HTTP/1.0\r\n"
				."Content-Type: application/json\r\n"
				."Content-Length: ".strlen($post)."\r\n"
				."Connection: Close\r\n\r\n"
				.$post;
		
		fwrite($fp, $http);
		//print fread($fp, 1000);
		fclose($fp);
	}
	
	/**
	 * Deletes the whole cache, and return FALSE on error.
	 */
	public function resetCache()
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
		if(KEY_DOMAIN && $flags == KEY_DOMAIN)
		{
			$key = $url['domain'].$key;
		}
		
		// check GETVARS flag
		if(KEY_GETVARS && $flags == KEY_GETVARS)
		{
			$key .= '?';
			$key .= (isset($url['query'])) ? $url['query'] : '';
		}
		
		// check SCHEME flag
		if(KEY_SCHEME && $flags == KEY_SCHEME)
		{
			$key = $url['scheme'].$key;
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