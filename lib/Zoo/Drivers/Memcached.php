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
namespace Zoo\Drivers;
use \Zoo\Config;
use \Zoo;

class Memcached implements Zoo\Driver
{
	function install() { }
    
    function __construct()
    {
        Zoo\Cache::log('Connect to memcached server at '.Config::get('memcached.host').':'.Config::get('memcached.port'));
        $this->m = new \Memcache;
        $this->m->connect(Config::get('memcached.host'), Config::get('memcached.port'));
    }
	
	function get($key)
	{		
		Zoo\Cache::log('Requesting item from memcached server');
        return $this->m->get('zoo:'.$key);
	}
	
	function store($key, $data, $timestamp, $size, $crc)
	{
        $data = array('data'=>$data, 'timestamp'=>$timestamp, 'size'=>$size, 'crc'=>$crc);
		$key = 'zoo:'.$key;
        
        Zoo\Cache::log('Storing item in memcached server');
        $result = $this->m->replace( $key, $data );
        Zoo\Cache::log('Replacing item '.($result? 'succeeded' :'failed').'... Trying set');
        if( $result == false )
        {
            $result = $this->m->set( $key, $data );
            Zoo\Cache::log('Setting item '.($result? 'succeeded' :'failed'));
        }
        return $result;
	}
	
	function reset()
	{
		$this->m->flush();
	}
}
?>