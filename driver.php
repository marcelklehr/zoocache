<?php
namespace Zoo;

interface Driver
{
	/**
	 * Install procedure. Leave empty if you don't need it!
	 */
	public function install();
	
	/**
	 * Should return an associative array with 4 items on success: array(data, timestamp, size, crc)
	 * and a boolean FALSE on error.
	 */
	public function getCache($key);
	
	/**
	 * Should store all 4 vars seperately under $key.
	 */
	public function storeCache($key, $data, $timestamp, $size, $crc);
}
?>