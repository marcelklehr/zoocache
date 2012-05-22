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
interface Zoo_Driver
{
	/**
	 * Install procedure. Leave empty if you don't need it!
	 */
	public function install();
	
	/**
	 * Should return an associative array with 4 items on success: array(data, timestamp, size, crc)
	 * and a boolean FALSE on error.
	 */
	public function get($key);
	
	/**
	 * Should store all 4 vars seperately under $key and return a boolean FALSE on error.
	 */
	public function store($key, $data, $timestamp, $size, $crc);
	
	/**
	 * Should delete all stored cache snapshots and return a boolean FALSE on error.
	 */
	public function resetCache();
}
?>