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

/**
 * Flags for createKey()
 */
define('Zoo\\USE_DOMAIN',  0x10000);
define('Zoo\\USE_PATH',    0x01000);
define('Zoo\\USE_GETVARS', 0x00100);
define('Zoo\\USE_SCHEME',  0x00010);

class Engine
{
	public $data;
	public $size;
	public $crc;
    
    public $cachingOn = TRUE;
    public $compressionOn = FALSE;
    public $expires = 20;
    public $blacklist = array();
	
	/**
	 * Takes some edits to the options
	 */
	public function __construct($key_gen)
	{
		header('X-Cache: Zoocache/'.Cache::VERSION);
        Cache::log('Plugins: '.join(', ',Config::get('filters')));
		
		$this->url = (@$_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
		$this->url .= $_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];
		$this->url.= (!empty($_SERVER['QUERY_STRING']))
						? '?'.$_SERVER['QUERY_STRING']
						: '';
		
		$this->key = self::createKey($this->url, $key_gen);
		$this->cache = Cache::item($this->key);
	}
	
	/* Set flags to define which variables should be used for creating the storage key.
     * Instead, you can also pass a callback to generate the key out of the URL passed to it.
     * possible flags: KEY_SCHEME, KEY_DOAMIN, KEY_GETVARS
     * Default value: 0
     */
	static function init($key_generator)
	{
		// Construct engine
		return new Engine($key_generator);
    }
    
    function compress()
    {
        $this->copmressionOn = TRUE;
        return $this;
    }
    
    function expireIn($secs)
    {
        $this->expires = $secs;
        return $this;
    }
    
    function filter($func)
	{
		if(!is_callable($func)) return false;
        $this->filters[] = $func;
	}
    
    /**
     * List all files you don't want to be cached using Reguar Expressions.
     * Your cache rule is checked against the whole ugly URL, after eventual rewrites: http://www.example.com/path/to/file.php?maybe=querystring
     * (NOT against: http://www.example.com/my/very/beautiful/uniform_resource_locator/)
     */
    function setBlacklist(array $list)
    {
        $this->blacklist = $list;
        return $this;
    }
    
    /**
     * Run the caching engine
     */
    function run()
    {
        // Force caching off when POST occured
		if (count($_POST) > 0)
			$this->cachingOn = FALSE;
		
		// Force caching off when blacklist matches
		foreach($this->blacklist as $entry)
		{
			if(FALSE == preg_match($entry, $this->url)) // FALSE: Error; 0: no matches;
				continue;
			
			Cache::log($this->url.' Matched blacklist entry: "'.$entry.'" Don\'t cache.');
			$this->cachingOn = FALSE;
			break;
		}
        
        // gzip compression?
        if($this->compressionOn === TRUE)
            ob_start('ob_gzhandler');
        
        if($this->cachingOn)
        {
            if(($c = $this->cache->get()) !== FALSE)
            {
              /* Found Cache */
                $this->data = $c['data'];
                $this->size = $c['size'];
                $this->crc = $c['crc'];
                
                // flush
                print $this->flush();
                exit;
            }
            Cache::log('Cache invalid');
        }
		
		// start output buffer and define callback
		ob_start(array($this, 'recache'));
		ob_implicit_flush(0);
	}
    
    /**
     * Apply all registered filters
     */
    function applyFilters($buffer)
    {
        foreach($this->filters as $filter)
        {
            $b = $filter($buffer);
            if($b !== FALSE) $buffer = $b;
        }
        return $buffer;
    }
	
    /**
     * Process the buffered data
     */
	function recache($chunk)
    {
        // Apply filters
        $chunk = $this->applyFilters($chunk);
        
        $this->size = strlen($chunk);
		$this->crc = crc32($chunk);
		$this->data = $chunk;
        
		// Store cache
		if(!connection_aborted() && $this->cachingOn)
		{
			$this->cache->store(array(
                'data' => $this->data,
                'size' => $this->size,
                'crc' => $this->crc
            ), $this->expires);
		}
        
		return $this->flush();
	}
	
	/**
	 * Handles Gzip and ETag to return the data with the right encoding
	 */
	function flush()
	{
		Cache::log('Flushing');
		
		// Build ETag
		$my_ETag = '"z' . $this->crc .'-'. $this->size . '"';
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
			return '';
			
		}else{
            // Something modified - return cached data
			return $this->data;
		}
	}
    
    /**
	 * Creates storage key with the options
	 */
	public static function createKey($url, $key_gen)
	{
	  /* Generate Script identification string */
		
		// call user-defined function
		if(is_callable($key_gen)) {
			return md5($key_gen($url));
		}
		
        $flags = $key_gen;
        
		$url = parse_url($url);
		
		$key = '';
		
		// check DOMAIN flag
		if((USE_DOMAIN & $flags) == USE_DOMAIN)
		{
			$key .= $url['host'];
		}
        
        // check PATH flag
		if((USE_PATH & $flags) == USE_PATH)
		{
			$key .= $url['path'];
		}
		
		// check GETVARS flag
		if((USE_GETVARS & $flags) == USE_GETVARS)
		{
			$key .= '?';
			$key .= (isset($url['query'])) ? $url['query'] : '';
		}
		
		// check SCHEME flag
		if((USE_SCHEME & $flags) == USE_SCHEME)
		{
			$key = $url['scheme'].'://'.$key;
		}
		
		return md5($key);
	}
}
?>