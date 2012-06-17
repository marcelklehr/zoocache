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
			zoo_timeout INT(11) NOT NULL ,
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
		
		Zoo\Cache::log($this->mysqli->error);
		$result->close();
        
        if($row['zoo_timeout'] < time())
            return FALSE;
		
		return $row['zoo_data'];
	}
	
	function store($key, $timeout, $data)
	{
		// check for existance
		$res = $this->mysqli->query("SELECT zoo_crc FROM zoocache WHERE zoo_key = '$key'");
		$num = $res->num_rows;
		$res->close();
		$data = $this->mysqli->escape_string($data);
		
		if($num < 1)
		{
		  /* insert new record */
			$res = $this->mysqli->query("INSERT INTO zoocache (zoo_key,zoo_data,zoo_timeout) VALUES ('$key', '$data', '$timeout')");
		}else{
		  /* update record */
			$res = $this->mysqli->query("UPDATE zoocache set zoo_data = '$data', zoo_timeout = '$timeout' WHERE zoo_key = '$key'");
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