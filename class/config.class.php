<?php
namespace Zoo;

class Config
{
	private static $directives = array(
		'caching' => TRUE,
		'gzip' => TRUE,
		'expire' => 600,
		'driver' => 'file',
		'blacklist' => array(),
		'keygeneration' => 0,
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
		if(!isset(self::$directives[$directive]))
			return null;
		return self::$directives[$directive];
	}
}
?>