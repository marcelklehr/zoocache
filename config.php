<?php
namespace Zoo;

/**
 * Enable caching?
 */
Cache::option('caching', true);

/**
 * Gzip output compression?
 */
Cache::option('gzip', true);

/**
 * Seconds until the cached data expires
 */
Cache::option('expire', 30);

/**
 * Storage driver to use
 */
Cache::option('driver', 'file');

/**
 * Enable caching of post requests?
 */
Cache::option('post', false);

/**
 * List all files you don't want to be cached. You can use RegEx-like ^ and $
 * Your cache rule is checked against the whole URI: http://www.example.com/path/to/file.php?maybe=querystring
 */
Cache::option('blacklist', array('test.php?nocache$'));

/**
 * Set flags to define which variables should be used for creating the storage key
 * KEY_DOAMIN, KEY_GETVARS, KEY_POSTVARS, KEY_COOKIES
 * Default value: 0
 */
Cache::option('keyflags', KEY_GETVARS);

/**
 * File driver options
 */
Cache::option('file.dir', ZOOCACHE_INC.'/tmp');

/**
 * mySQL driver options
 */
Cache::option('mysql.host', 'localhost');
Cache::option('mysql.user', '');
Cache::option('mysql.password', '');
Cache::option('mysql.database', '');

/**
 * Run in debug mode?
 */
Cache::option('debug', true);
?>