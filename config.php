<?php
use Zoo\Config;

/**
 * Storage driver to use
 */
Config::set('driver', 'file');

/**
 * File driver options
 */
Config::set('file.dir', dirname(__FILE__).'/tmp');

/**
 * Mysql driver options
 */
Config::set('mysql.host', 'localhost');
Config::set('mysql.user', '');
Config::set('mysql.password', '');
Config::set('mysql.database', '');

/**
 * memcached driver options
 */
Config::set('memcached.host', 'localhost');
Config::set('memcached.port', 11211);

/**
 * Run in debug mode?
 */
Config::set('debug', true);
?>