<?php
namespace Zoo;

/**
 * Enable caching?
 */
Config::set('caching', true);

/**
 * Gzip output compression?
 */
Config::set('gzip', true);

/**
 * Seconds until the cached data expires
 */
Config::set('expire', 300);

/**
 * Storage driver to use
 */
Config::set('driver', 'file');

/**
 * Enable caching of post requests?
 */
Config::set('post', false);

/**
 * List all files you don't want to be cached using Reguar Expressions.
 * Your cache rule is checked against the whole URI: http://www.example.com/path/to/file.php?maybe=querystring
 */
Config::set('blacklist', array('~test\.php\?nocache$~'));

/**
 * Set flags to define which variables should be used for creating the storage key
 * KEY_DOAMIN, KEY_GETVARS, KEY_POSTVARS, KEY_COOKIES
 * Default value: 0
 */
Config::set('keygeneration', KEY_GETVARS);

/**
 * File driver options
 */
Config::set('file.dir', ZOOCACHE_INC.'/tmp');

/**
 * mySQL driver options
 */
Config::set('mysql.host', 'localhost');
Config::set('mysql.user', '');
Config::set('mysql.password', '');
Config::set('mysql.database', '');

/**
 * Run in debug mode?
 */
Config::set('debug', true);
?>