<?php
/**
 * Enable caching?
 */
Zoo_Config::set('caching', true);


/**
 * Seconds until the cached data expires
 */
Zoo_Config::set('expire', 11);

/**
 * Storage driver to use
 */
Zoo_Config::set('driver', 'file');

/**
 * Enable plugins
 * Available out of the box: htmlmin
 */
Zoo_Config::set('plugins', array(
    'htmlmin'
));

/**
 * Set to true, if you want your output to be gzipped
 */
Zoo_Config::set('gzip', true);

/**
 * List all files you don't want to be cached using Reguar Expressions.
 * Your cache rule is checked against the whole ugly URL, after eventual rewrites: http://www.example.com/path/to/file.php?maybe=querystring
 * (NOT against: http://www.example.com/my/very/beautiful/uniform_resource_locator/)
 */
Zoo_Config::set('blacklist', array(
    '~test\.php\?nocache$~'
));

/**
 * Set flags to define which variables should be used for creating the storage key.
 * Instead, you can also pass a callback to generate the key out of the URL passed to it.
 * possible flags: KEY_SCHEME, KEY_DOAMIN, KEY_GETVARS
 * Default value: 0
 */
Zoo_Config::set('keygenerator', KEY_GETVARS);

/**
 * File driver options
 */
Zoo_Config::set('file.dir', ZOOCACHE_INC.'/tmp');

/**
 * mySQL driver options
 */
Zoo_Config::set('mysql.host', 'localhost');
Zoo_Config::set('mysql.user', '');
Zoo_Config::set('mysql.password', '');
Zoo_Config::set('mysql.database', '');

/**
 * Run in debug mode?
 */
Zoo_Config::set('debug', true);
?>