<?php
namespace Zoo;

/**
 * Enable caching?
 */
Config::set('caching', true);

/**
 * Enable gzip output compression?
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
 * List all files you don't want to be cached using Reguar Expressions.
 * Your cache rule is checked against the whole URI: http://www.example.com/path/to/file.php?maybe=querystring
 */
Config::set('blacklist', array('~test\.php\?nocache$~'));

/**
 * Set flags to define which variables should be used for creating the storage key.
 * Instead, you can also pass a callback to generate the key out of the URL passed to it.
 * possible flags: KEY_SCHEME, KEY_DOAMIN, KEY_GETVARS
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