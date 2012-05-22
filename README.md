# Intelligent output caching that neatly integrates into your application #
Zoocache uses output buffering to cache your pages. It then stores the output under a storage key, which is generated using certain request parameters (such as the script name, the URL scheme, the domain or the querystring).  
Everytime a request ends up generating this storage key, the cached data is retrieved from the storage medium and flushed to the client, until the cache entry expires and is recached.  
By defining, which parameters are used to generate the storage key, you can control, what is shown to various user types. You can tell zoocache for example to use the value of a specific cookie for storage key generation, so that all users, whose cookie holds the same value, see the same page.  

To be usable in all environments, Zoocache abstracts storage operations in Driver classes, so that it's possible to run Zoocache with the database of your choice. Also, it's very easy to implement support for a new storage medium.

Aditionally Zoocache allows you to apply filters to the cached data, using [Plugins](http://github.com/marcelklehr/zoocache/wiki/Plugins). The HTML minifier plugin `htmlmin`, for example, strips out all unnecessary whitespaces from the cached document.

If you want to use Zoocache in your web app, there is a simple [API for accessing and modifying the cached contents](http://github.com/marcelklehr/zoocache/wiki/API) of arbitrary pages, natively from within your application.

### Download ###

* Download the latest version [here](http://github.com/marcelklehr/zoocache/zipball/master) and follow [these steps](https://github.com/marcelklehr/zoocache/wiki)
* See the [wiki](http://github.com/marcelklehr/zoocache/wiki) for more information
* Submit any questions, bugs or suggestions to the [Issue Tracker](http://github.com/marcelklehr/zoocache/issues)

### Features ###
+ Various storage drivers (currently file system and MySQL are supported -- you can easily write your own)
+ GZip compression
+ Easily extendable through Plugins (e.g. HTML-minimization)
+ Caching blacklist (with Regular Expressions)
+ Various options for storage key generation (e.g. Cache different pages for registered and unregistered users)
+ API to modify the cache natively in your application

### Requirements ###
* `PHP 5.3` or newer
* optionally `zlib` for gzip compression

## Tips ##
* If your browser can't view the page and reports an encoding error, there's probably a PHP error messing up the gzip encoding. Turning off `gzip` compression in the config file should enable you to see the error.
* In `debug mode` Zoocache logs all actions to the HTTP response header, but in a production environment it's recommended to turn it off in `config.php`.
* If you don't want to copy the bootstrap line in all files, you can also add an `auto_prepend` directive to your .htaccess file: `php_value auto_prepend_file /zoocache/bootstrap.php`
* Features like a web statistics tool, can be implemented above the boostrap: `include 'bootstrap.php;`. The code above it will then be excecuted everytime the page is shown.