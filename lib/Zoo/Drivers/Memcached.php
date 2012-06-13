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