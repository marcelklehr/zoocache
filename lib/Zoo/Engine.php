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
		header('X-Cache: Zoocache/'.Cache::VERSION);
        Cache::log('Plugins: '.join(', ',Config::get('filters')));
		
		$this->cache = Cache::init();
		
		// Force caching off when POST occured
		if (count($_POST) > 0)
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
        
        // gzip compression?
        if(Config::get('gzip') === TRUE)
            ob_start('ob_gzhandler');
		
		// caching on?
		if(!Config::get('caching'))
            return;
        
        if(($c = $engine->cache->get()) !== FALSE)
        {
          /* Found Cache */
            $engine->data = $c['data'];
            $engine->size = $c['size'];
            $engine->crc = $c['crc'];
            
            // valid?
            if(time() < $c['timestamp'] + Config::get('expire'))
            {
                // flush
                print $engine->flush();
                exit;
            }
        }
        Cache::log('Cache invalid');
		
		// start output buffer and define callback
		ob_start(array($engine, 'recache'));
		ob_implicit_flush(0);
	}
	
    /**
     * Process the buffered data
     */
	function recache($chunk)
    {
        // Apply filters
        $chunk = Cache::filter($chunk);
        
		// Store cache
		if(!connection_aborted() && Config::get('caching'))
		{
			$this->cache->store($chunk);
		}
		
		$this->size = strlen($chunk);
		$this->crc = crc32($chunk);
		$this->data = $chunk;
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
}
?>