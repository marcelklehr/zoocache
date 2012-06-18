<?php
use Zoo\Config;

/**
 * Storage driver to use
 */
Config::set('driver', 'File');

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
 * When enabled, all actions will be logged to the HTTP response header, but in a production environment it's recommended to turn it off.
 */
Config::set('debug', true);
?>