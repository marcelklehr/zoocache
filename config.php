<?php
use Zoo\Config;

/**
 * Enable caching?
 */
Config::set('caching', true);


/**
 * Seconds until the cached data expires
 */
Config::set('expire', 300);

/**
 * Storage driver to use
 */
Config::set('driver', 'file');

/**
 * Enable filters
 * Available out of the box: htmlmin
 */
Config::set('filters', array(
    'htmlmin'
));

/**
 * Set to true, if you want your output to be gzipped
 */
Config::set('gzip', true);

/**
 * List all files you don't want to be cached using Reguar Expressions.
 * Your cache rule is checked against the whole ugly URL, after eventual rewrites: http://www.example.com/path/to/file.php?maybe=querystring
 * (NOT against: http://www.example.com/my/very/beautiful/uniform_resource_locator/)
 */
Config::set('blacklist', array(
    '~test\.php\?nocache$~'
));

/**
 * Set flags to define which variables should be used for creating the storage key.
 * Instead, you can also pass a callback to generate the key out of the URL passed to it.
 * possible flags: KEY_SCHEME, KEY_DOAMIN, KEY_GETVARS
 * Default value: 0
 */
Config::set('keygenerator', KEY_GETVARS);

/**
 * File driver options
 */
Config::set('file.dir', dirname(__FILE__).'/tmp');

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