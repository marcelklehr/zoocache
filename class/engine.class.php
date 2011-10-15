<?php
namespace Zoo;

class Engine
{
	public $data;
	public $size;
	public $crc;
	
	/**
	 * Takes some edits to the options
	 */
	public function __construct()
	{
		header('X-Cache: Zoocache/'.ZOOCACHE_VER);
		
		$this->cache = Cache::init();
		
		// Force gzip off if gzcompress does not exist
		if(!function_exists('gzcompress'))
			Config::set('gzip', FALSE);
		
		// Force caching off when POST occured and you don't want it cached
		if (!Config::get('post') AND count($_POST) > 0)
			Config::set('caching', FALSE);
		
		// Force caching off when blacklist matches
		$location = $this->cache->url;
		$list = Config::get('blacklist');
		foreach($list as $entry)
		{
			if(FALSE == preg_match($entry, $location)) // FALSE: Error; 0: no matches;
				continue;
			
			Cache::log($location.' Matched blacklist entry: "'.$entry.'" Don\'t cache');
			Config::set('caching', FALSE);
			break;
		}
	}
	
	/**
	 * Initializes the caching process, loads the driver and coordinates everything
	 */
	static function init()
	{
		// Construct engine
		$engine = new Engine();
		
		// Nocache flag?
		if(@$_SERVER['HTTP_REFERER'] === $engine->cache->key)
			return;
		
		// caching on?
		if(!Config::get('caching'))
			return;
		
		if(($c = $engine->cache->getCache()) !== FALSE)
		{
		  /* Found Cache */
			$engine->data = $c['data'];
			$engine->size = $c['size'];
			$engine->crc = $c['crc'];
			
			// valid?
			if(time() > $c['timestamp'] + Config::get('expire'))
			{
				Cache::log('Stored cache is invalid');
				
				// Delegate to monkey but load invalid cache once more
				$engine->cache->recache();
			}
			
			// flush
			print $engine->flush();
			exit;
		}else{
		  /* No Cache found */
			// Delegate to monkey and let the page load normally
			$engine->cache->recache();
		}
	}
	
	/**
	 * Handles Gzip and ETag to return the data with the right encoding
	 */
	function flush()
	{
		Cache::log('Flushing');
		
		// Build ETag
		$my_ETag = '"z' . $this->crc . $this->size . '"';
		header('ETag: '.$my_ETag);
		
		$received_ETag = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) : '';
		
		// Check if anything is modified
		if(strpos($received_ETag, $my_ETag) !== FALSE)
		{
		  /* Nothing modified - do nothing */
			if(stripos($_SERVER['SERVER_SOFTWARE'], 'microsoft') !== FALSE)
			{
				// IIS has already sent HTTP 200
				header('Status: 304 Not Modified');
			}else{
				header('HTTP/1.0 304');
			}
			return;
			
		}else{
		  /* Something modified - return cached data */
			if(!Config::get('gzip'))
			{
			  /* gzip off */
				// Just output cache data
				return $this->data;
				
			}else{
			  /* gzip on */
				if(($encoding = self::getBrowserEncoding()) !== FALSE)
				{
				  /* Client allows gzip */
					header('Content-Encoding: '.$encoding);
					$gzip = substr($this->data, 0, $this->size);
					$data = "\x1f\x8b\x08\x00\x00\x00\x00\x00" . $gzip;
				}else{
				  /* Client doesn't allow gzip */
					// Uncompress cached data
					$data = gzuncompress($this->data);
				}
				return $data;
			}
		}
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