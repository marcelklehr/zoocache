<?php
namespace Zoo;

/**
 * Flags for Cache::init()
 */
define('KEY_DOMAIN', 8);  //1000
define('KEY_GETVARS', 4); //0100
define('KEY_POSTVARS', 2);//0010
define('KEY_COOKIES', 1); //0001

class Cache
{
	/**
	 * My private copy of the config
	 */
	private static $options = array(
		'caching' => TRUE,
		'gzip' => TRUE,
		'expire' => 600,
		'driver' => 'file',
		'post' => FALSE,
		'blacklist' => array(),
		'keyflags' => 0,
		'debug' => FALSE,
		'firstrun' => FALSE
	);
	
	/**
	 * The driver object
	 */
	public static $driver;
	
	/**
	 * The storage key of the current entity
	 */
	public $key;
	
	/**
	 * The cache properties
	 */
	public $cache = array(
		'data' => NULL,
		'size' => NULL,
		'crc' => NULL
	);
	
	/**
	 * Takes some edits to the options
	 */
	public function __construct(Driver $driver)
	{
		header('X-Cache: Zoocache/'.ZOOCACHE_VER);
		
		$this->key = self::createStorageKey();
		
		self::log('Storage key is '.$this->key);
		
		// Force gzip off if gzcompress does not exist
		if(!function_exists('gzcompress'))
			self::option('gzip', FALSE);
		
		// Force caching off when POST occured and you don't want it cached
		if (!self::option('post') AND count($_POST) > 0)
			self::option('caching', FALSE);
		
		// Force caching off when blacklist matches
		$location = '^http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];
		if(KEY_GETVARS && self::option('keyflags') == KEY_GETVARS && !empty($_SERVER['QUERY_STRING']))
		{
			$location .= '?'.$_SERVER['QUERY_STRING']; // add qery string, when getvars flag set
		}
		$location .= '$';
		foreach(self::$options['blacklist'] as $entry)
		{
			if(FALSE === stripos($location, $entry))
				continue;
			self::log($location.' Matched blacklist entry: "'.$entry.'" Don\'t cache');
			self::option('caching', FALSE);
		}
	}
	
	/**
	 * Initializes the caching process, loads the driver and coordinates everything
	 */
	static function init()
	{
		// Load driver
		include ZOOCACHE_INC. '/drivers/' . self::option('driver') . '.php';
		
		// Install driver on first run
		if(self::option('firstrun'))
		{
			$driver = self::option('driver');
			if(self::$driver->install() === FALSE)
			{
				die("Hmm, something went wrong during the installation process of the $driver driver. Please, check your $driver. driver settings in 'config.php'!");
			}
			die("Successfully installed $driver driver. Ready for caching!");
		}
		
		// Load engine
		$engine = new Cache(self::$driver);
		
		if($engine->getCache() !== FALSE)
		{
		  /* Found Cache */
			// flush
			print $engine->flush();
		}else{
		  /* No Cache found */
			self::log('No cache for this page');
			// Generate page and wait for callback
			ob_start(array($engine,'cache'));
			ob_implicit_flush(0);
		}
	}
	
	/**
	 * Easy access to options
	 */
	static function option($var, $val=null)
	{
		if(isset($val))
			self::$options[$var] = $val;
		
		return self::$options[$var];
	}
	
	/**
	 * Calls the driver for storing cached content
	 */
	function storeCache()
	{		
		$c = $this->cache;
		$c['timestamp'] = time();
		
		if($c['size'] <= 0)
		{
			self::log('Empty output buffer. don\'t save');
			return;
		}
		
		self::$driver->storeCache($this->key, $c['data'], $c['timestamp'], $c['size'], $c['crc']);
	}
	
	/**
	 * Calls the driver for restoring the cacshed content. Checks validity.
	 */
	function getCache()
	{
		// return FALSE, if caching is disabled
		if(!self::option('caching'))
			return FALSE;
		
		$c = self::$driver->getCache($this->key);
		
		if($c === FALSE)
		{
			self::log('Couldn\'t get cache data');
			return false;
		}
		
		// validate
		if(time() > $c['timestamp'] + self::option('expire'))
		{
			self::log('Stored cache is invalid');
			return false;
		}
		
		// set local 
		$this->cache = array(
			'data' => $c['data'],
			'size' => $c['size'],
			'crc' => $c['crc']
		);
	}
	
	/**
	 * The callback after output buffering. Caches the buffered content.
	 */
	function cache($content)
	{
		$c['size'] = strlen($content);
		$c['crc'] = crc32($content);
		
		$c['data'] = (self::option('gzip'))
						? gzcompress($content)
						: $content;
		
		$this->cache = $c;
		
		// Don't write if connection aborted or caching is disabled
		if(!connection_aborted() && self::option('caching'))
		{
			self::log('Creating new cache');
			$this->storeCache();
		}
		
		return $this->flush();
	}
	
	/**
	 * Handles Gzip and ETag to return the data with the right encoding
	 */
	function flush()
	{
		self::log('Flushing');
		
		// Build ETag
		$my_ETag = '"z'.$this->cache['crc'].$this->cache['size'].'"';
		header('ETag: '.$my_ETag);
		
		$received_ETag = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) : "";
		
		// Check if anything is modified
		if(strpos($received_ETag, $my_ETag) !== FALSE)
		{
		  /* Nothing modified - do nothing */
			if(stripos($_SERVER['SERVER_SOFTWARE'], 'microsoft') !== FALSE)
			{
				// IIS has already sent 'HTTP/1.1 200' at this point
				header('Status: 304 Not Modified');
			}else{
				header('HTTP/1.0 304');
			}
			return;
			
		}else{
		  /* Something modified - return cached data */
			if(!self::option('gzip'))
			{
			  /* gzip off */
				// Just output cache data
				return $this->cache['data'];
				
			}else{
			  /* gzip on */
				if(($encoding = self::getBrowserEncoding()) !== FALSE)
				{
				  /* Client allows gzip */
					header('Content-Encoding: '.$encoding);
					$gzip = substr($this->cache['data'], 0, $this->cache['size']);
					$data = "\x1f\x8b\x08\x00\x00\x00\x00\x00" . $gzip;
				}else{
				  /* Client doesn't allow gzip */
					// Uncompress cached data
					$data = gzuncompress($this->cache['data']);
				}
				return $data;
			}
		}
	}
	
	/**
	 * Creates storage key with the options and stores it in $this->key
	 */
	static function createStorageKey()
	{
	  /* Generate Script identification string */
		$flags = self::option('keyflags');
		
		$key = $_SERVER['PHP_SELF'];
		
		// check DOMAIN flag
		if(KEY_DOMAIN && $flags == KEY_DOMAIN)
		{
			$key = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];
		}
		
		// check GETVARS flag
		if(KEY_GETVARS && $flags == KEY_GETVARS)
		{
			$key .= '|GET='.serialize($_GET);
		}
		
		// check POSTVARS flag
		if(KEY_POSTVARS && $flags == KEY_POSTVARS)
		{
			$key .= '|POST='.serialize($_POST);
		}
		
		// check COOKIES flag
		if(KEY_COOKIES && $flags == KEY_COOKIES)
		{
			$key .= '|COOKIE='.serialize($_COOKIE);
		}
		
		return md5($key);
	}
	
	/**
	 * Outputs every log statement to the HTTP header when in debug mode
	 */
	static function log($string)
	{
		static $c = 0;
		if(self::option('debug'))
		{
			header("X-Zoocache-Log-$c: ".$string, true);
		}
		$c++;
	}
	
	/**
	 * Detects, whether the bowser accepts gzip
	 */
	static function getBrowserEncoding()
	{
		if (headers_sent() || connection_aborted())
			return false;
			
		if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'x-gzip') !== false)
			return 'x-gzip';
		
		if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') !== false)
			return 'gzip';
		
		return false;
	}
}
?>