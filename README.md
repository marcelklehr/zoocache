# Zoocache #
A intelligent output caching tool, which is very flexible, easily extendable, and neatly integrates into your application.
It is freely licensed under the terms of the **GNU General Public License**.

## Requirements ##
* `PHP 5.3` or newer
* optionally `zlib` (for gzip plugin)

## Features ##
+ Different storage drivers (currently file system & MySQL)
+ Easily extendable through Plugins (e.g. GZip compression, HTML-minimization)
+ Caching blacklist (RegEx)
+ Various options for storage key generation
+ API to retrieve or reset the cache natively in your application

## Set-up in 5 easy steps ##
1. Download the latest version [here](http://github.com/marcelklehr/zoocache/tags) and copy the `/zoocache` folder into your project's directory.  
2. First, edit the options (located in `/zoocache/options.php`): choose a caching driver, define the cache timeout, enable plugins etc. 
3. Now, open `install.php` in your browser. You should see a short message, which states that the chosen driver was installed successfully. If not, check your driver settings.  
4. When everything is fine so far, load `test.php` with your browser to check whether zoocache is working.  
5. Finally, Include the `bootstrap.php` file at the **top** of all files you want to cache. Any output above this include statement may lead to unforeseen results! Alternatively you can also add an auto_prepend directive to your `.htaccess` file: `php_value auto_prepend_file /path/to/zoocache/bootstrap.php`.

## API Integration ##
If you'd like to integrate Zoocache into your application, just...

1. Load the Zoocache API with `include '/path/to/zoocache/api.php';`  
2. To init the API, run `$zooapi = Zoo\Cache::init($url);` passing the URL of the page you want to access.

Now you can poke around with the cache entry of the passed URL:

* Run `$zooapi->getCache();` to retrieve the current contents of the cache by invoking .
* Invoke `$zooapi->storeCache($new_contents);` to insert/replace contents, passing the new contents as a string.
* Execute `$zooapi->resetCache();` to force Zoocache to recache this particular cache on the next request.
* Run `$zooapi->reset();` to reset all cached pages.
 
## Good to know ##
* You can easily build a storage driver yourself, if the database system you are using is not implemented.
* In `debug mode` you can track all errors with the HTTP response headers, but in production environments it's recommended to turn it off.
* If you want to integrate features like a web statistics tool, you can do this above the line `include 'bootstrap.php;`. It will then be excecuted everytime the page is shown, although an eventual counter might not change, because the output is cached.
* If you output anything above the line `include 'bootstrap.php;` this part will be shown several times after some time, increasing with caching of that page.

## See also ##
* Submit any bug or suggestion to the [Issue Tracker](http://github.com/marcelklehr/zoocache/issues)
* Download the latest version [here](https://github.com/marcelklehr/zoocache/tags)