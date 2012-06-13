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

class Config
{
	private static $directives = array(
		'caching' => TRUE,
		'expire' => 300,
        'filters' => array(),
        'gzip' => TRUE,
		'driver' => 'file',
		'blacklist' => array(),
		'keygenerator' => 0,
		'debug' => FALSE,
		'firstrun' => FALSE
	);
	
	public static function set($directive, $value)
	{		
		self::$directives[$directive] = $value;
		return TRUE;
	}
	
	public static function get($directive)
	{
		return (isset(self::$directives[$directive]))? self::$directives[$directive] : null;
	}
}
?>