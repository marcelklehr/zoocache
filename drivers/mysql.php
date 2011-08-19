<?php
namespace Zoo\drivers;
use \Zoo;

class Mysql implements Zoo\Driver
{
	public $mysqli;
	
	function __construct()
	{
		$this->mysqli = new \mysqli(Zoo\Cache::option('mysql.host'), Zoo\Cache::option('mysql.user'), Zoo\Cache::option('mysql.password'), Zoo\Cache::option('mysql.database'));
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
	
	function getCache($key)
	{		
		$result = $this->mysqli->query("SELECT * FROM zoocache WHERE zoo_key = '$key'");
		
		if(($row = $result->fetch_assoc()) === FALSE)
			return FALSE;
		
		$return = array(
			'data' =>      $row['zoo_data'],
			'timestamp' => $row['zoo_timestamp'],
			'size' =>      $row['zoo_size'],
			'crc' =>       $row['zoo_crc']
		);
		
		$result->close();
		
		return $return;
	}
	
	function storeCache($key, $data, $timestamp, $size, $crc)
	{
		$res = $this->mysqli->query("SELECT zoo_crc FROM zoocache WHERE zoo_key = '$key'");
		if($res->num_rows < 1)
		{
		  /* innsert new record */
			$this->mysqli->query("INSERT INTO zoocache (zoo_key,zoo_data,zoo_timestamp,zoo_size,zoo_crc) VALUES ('$key', '$data', '$timestamp', '$size', '$crc')");
		}else{
		  /* update record */
			$this->mysqli->query("UPDATE zoocache set zoo_data = '" . mysqli_escape_string($data) . "', zoo_timestamp = '$timestamp', zoo_size = '$size', zoo_crc = '$crc' WHERE zoo_key = '$key'");
		}
		Zoo\Cache::log($this->mysqli->error);
		$res->close();
		return $return;
	}
}

/**
 * Register Driver
 */
Zoo\Cache::$driver = new Mysql;
?>