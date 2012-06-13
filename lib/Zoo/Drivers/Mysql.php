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
use \Zoo;

class Mysql implements Zoo\Driver
{
	public $mysqli;
	
	function __construct()
	{
		$this->mysqli = new \mysqli(Zoo\Config::get('mysql.host'), Zoo\Config::get('mysql.user'), Zoo\Config::get('mysql.password'), Zoo\Config::get('mysql.database'));
	}
	
	function install()
	{
		if($this->mysqli->query("CREATE TABLE zoocache (
			zoo_key VARCHAR(255) NOT NULL ,
			zoo_data BLOB,
			zoo_timestamp INT(11) NOT NULL ,
			zoo_size INT(11),
			zoo_crc INT,
			PRIMARY KEY(zoo_key)
		)") === FALSE)
		{
			print($this->mysqli->error."<br>\r\n");
			return FALSE;
		}
		return TRUE;
	}
	
	function get($key)
	{		
		$result = $this->mysqli->query("SELECT * FROM zoocache WHERE zoo_key = '$key'");
		
		if($result->num_rows < 1)
			return FALSE;
		
		if(($row = $result->fetch_assoc()) === FALSE)
			return FALSE;
		
		$return = array(
			'data' =>      $row['zoo_data'],
			'timestamp' => $row['zoo_timestamp'],
			'size' =>      $row['zoo_size'],
			'crc' =>       $row['zoo_crc']
		);
		
		Zoo\Cache::log($this->mysqli->error);
		$result->close();
		
		return $return;
	}
	
	function store($key, $data, $timestamp, $size, $crc)
	{
		// check for existance
		$res = $this->mysqli->query("SELECT zoo_crc FROM zoocache WHERE zoo_key = '$key'");
		$num = $res->num_rows;
		$res->close();
		$data = $this->mysqli->escape_string($data);
		
		if($num < 1)
		{
		  /* insert new record */
			$res = $this->mysqli->query("INSERT INTO zoocache (zoo_key,zoo_data,zoo_timestamp,zoo_size,zoo_crc) VALUES ('$key', '$data', '$timestamp', '$size', '$crc')");
		}else{
		  /* update record */
			$res = $this->mysqli->query("UPDATE zoocache set zoo_data = '$data', zoo_timestamp = '$timestamp', zoo_size = '$size', zoo_crc = '$crc' WHERE zoo_key = '$key'");
		}
		
		Zoo\Cache::log($this->mysqli->error);
		$res->close();
		
		return $return;
	}
	
	function reset()
	{
		$res = $this->mysqli->query("TRUNCATE TABLE zoocache");
		
		$errno = $this->mysqli->errno;
		Zoo\Cache::log($this->mysqli->error);
		$res->close();
		
		return ($errno === 0);
	}
}
?>