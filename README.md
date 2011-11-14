# Zoocache #
Easy, extendable and intelligent output caching tool, that neatly integrates into your application.
Freely licensed under the terms of the **GNU General Public License**.

## Requirements ##
* `PHP 5.3` or newer
* (`zlib` for gzip compression)

## Features ##
+ Different storage drivers (currently file system & MySQL)
+ GZip compression
+ Easily extendable through Plugins (e.g. HTML-minimization)
+ Caching blacklist (with Regular Expressions)
+ Various options for storage key generation
+ API to retrieve or reset the cache natively in your application

## Set-up in 5 easy steps ##
1. Download the latest version [here](http://github.com/marcelklehr/zoocache/tags) and copy the `/zoocache` folder into your project's directory.  
2. First, edit the config file (located at `/zoocache/config.php`): choose a caching driver, define the cache timeout, enable plugins etc. 
3. Now, open `install.php` in your browser. You should see a short message, which states that the chosen driver was installed successfully. If not, check your driver settings.  
4. When everything is fine so far, load `test.php` with your browser to check whether zoocache is working.  
5. Finally, Include the `bootstrap.php` file at the top of all files you want to cache. Any output above this include statement will not be cached!

## API Integration ##
The Zoocache API is a simple programming interface to manipulate the cached contents in another application.
Find out more about the Zoocache API in the [wiki](http://github.com/marcelklehr/zoocache/wiki/API).

## Drivers ##
In Zoocache a driver abstracts the access to a storage medium. The MySQL driver, for example, enables Zoocache to store the cache data in a MySQL database.
If the storage system you are using isn't implemented, see the [wiki](http://github.com/marcelklehr/zoocache/wiki/Drivers) on how to easily build one yourself.

## Plugins ##
Plugins in Zoocache apply filters to the cached data. For example, the HTML minifier plugin `htmlmin` strips out all unnecessary whitespaces from the cached document.
Find out more about plugins in the [wiki](http://github.com/marcelklehr/zoocache/wiki/Plugins).

## Good to know ##
* If your browser can't veiw the page and reports an encoding error, there's probably a PHP error messing up the gzip encoding. Turning off `gzip` compression in the config file should enable you to see the error.
* In `debug mode` Zoocache logs all actions in the HTTP response headers, but in a production environment it's recommended to turn it off in the config file.
* If you don't want to copy the bootstrap line in all files, you can also add an `auto_prepend` directive to your .htaccess file: `php_value auto_prepend_file /zoocache/bootstrap.php`
* Features like a web statistics tool, can be implemented above the boostrap: `include 'bootstrap.php;`. The code above it will then be excecuted everytime the page is shown.

## See also ##
* See the [wiki](http://github.com/marcelklehr/zoocache/wiki) for more information
* Download the latest version [here](http://github.com/marcelklehr/zoocache/tags)
* Submit any bug or suggestion to the [Issue Tracker](http://github.com/marcelklehr/zoocache/issues)