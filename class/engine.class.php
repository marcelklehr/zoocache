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
        Cache::log('Plugins: '.json_encode(Config::get('plugins')));
		
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
        
        if(($c = $engine->cache->getCache()) !== FALSE)
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
			$this->cache->storeCache($chunk);
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