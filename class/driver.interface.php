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