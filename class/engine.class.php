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
class Zoo_Engine
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
        Zoo_Cache::log('Plugins: '.json_encode(Zoo_Config::get('plugins')));
		
		$this->cache = Zoo_Cache::init();
		
		// Force caching off when POST occured
		if (count($_POST) > 0)
			Zoo_Config::set('caching', FALSE);
		
		// Force caching off when blacklist matches
		$location = $this->cache->url;
		$list = Zoo_Config::get('blacklist');
		foreach($list as $entry)
		{
			if(FALSE == preg_match($entry, $location)) // FALSE: Error; 0: no matches;
				continue;
			
			Zoo_Cache::log($location.' Matched blacklist entry: "'.$entry.'" Don\'t cache');
			Zoo_Config::set('caching', FALSE);
			break;
		}
	}
	
	/**
	 * Initializes the caching process, loads the driver and coordinates everything
	 */
	static function init()
	{
		// Construct engine
		$engine = new Zoo_Engine();
        
        // gzip compression?
        if(Zoo_Config::get('gzip') === TRUE)
            ob_start('ob_gzhandler');
		
		// caching on?
		if(Zoo_Config::get('caching'))
        {
            if(($c = $engine->cache->get()) !== FALSE)
            {
              /* Found Zoo_Cache */
                $engine->data = $c['data'];
                $engine->size = $c['size'];
                $engine->crc = $c['crc'];
                
                // valid?
                if(time() < $c['timestamp'] + Zoo_Config::get('expire'))
                {
                    // flush
                    print $engine->flush();
                    exit;
                }
            }
            Zoo_Cache::log('Cache invalid');
        }else Zoo_Cache::log('Caching disabled');
		
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
        $chunk = Zoo_Cache::filter($chunk);
        
		// Store cache
		if(!connection_aborted() && Zoo_Config::get('caching'))
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
		Zoo_Cache::log('Flushing');
		
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