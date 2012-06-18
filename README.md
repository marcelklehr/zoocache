# Caching made easy #
Zoocache is a caching library utilising various storage mediums (Memcached, MySQL and File System), intended to cache expensive task in your application or website (e.g. database queries).

Additionally Zoocache provides a throw-in caching engine, that uses output buffering to easily add a caching layer to any existing website.  

To be usable in all environments, Zoocache extracts storage operations into various storage drivers, so that it's possible to run Zoocache with the database of your choice. Additionally, this makes it very easy to add support for a new storage medium.

### Requirements ###
* `PHP 5.3` or newer
* zlib` for gzip compression (optional)

### Installation ###
1. Install Zoocache through [composer](http://getcomposer.org/) using `'zoocache/zoocache': '*'` as a [dependency](http://getcomposer.org/doc/00-intro.md#declaring-dependencies) in your `composer.json`.
2. Edit `vendor/zoocache/zoocache/config.php` and set the options for the storage medium of your choice.
3. Load `vendor/zoocache/zoocache/install.php` with your browser or run it on the command line using `php vendor/zoocache/zoocache/install.php`.
4. Set up a php file in your app directory and include `vendor/zoocache/zoocache/test.php`. Now, load the new file with your browser to check whether zoocache is working.

### Links
 * Documentation can be found in the [wiki](https://github.com/marcelklehr/zoocache/wiki)
 * Submit any questions, bugs or suggestions to the [Issue Tracker](https://github.com/marcelklehr/zoocache/issues)

### License ###
Copyright 2011-2012 by Marcel Klehr  
MIT License.