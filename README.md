# Zoocache #
Zoocache is the intelligent output caching tool you wished to have ever since for your PHP application.

## Features ##
+ GZip compression
+ Different storage drivers (currently file, mysql - easily extendable)
+ Blacklist (RegEx)
+ Options for storage key generation
+ API to reset the cache from within your application
+ High performance, due to non-blocking caching

## Set-up in 5 easy steps ##
1. Download the latest version [here](http://github.com/marcelklehr/zoocache/tags) and copy the `/zoocache` folder into your project's directory.  
2. Alter the options (located in `/zoocache/options.php`) to your wishes and choose a caching driver.  
3. Now, open `install.php` in your browser. You should see a short message, which states that the chosen driver was installed successfully. If not, check your driver settings.  
4. When everything is fine so far, load `test.php` with your browser to check whether zoocache is working.
5. Include the `bootstrap.php` file at the **top** of all files you want to cache. Any output above this include statement may lead to unforeseen results! Alternatively you can also add an auto_prepend directive in your `.htaccess` file: `php_value auto_prepend_file /path/to/bootstrap.php`.

## API Integration ##
If you want to integrate Zoocache into your application, just follow these steps:  
1. Load the Zoocache API with `include '/path/to/zoocache/api.php';`  
2. To init the API, run `$zooapi = Zoo\Cache::init($url);` passing the URL of the page you want to access.

Now you can poke around with the cache entry of the passed URL. Here's a list of what you can do with it:

* You can retrieve the current contents of the cache by invoking `$zooapi->getCache();`.
* You can replace and insert contents by invoking `$zooapi->storeCache($new_contents);` passing the new contents as a string.
* You can force Zoocache to recache this particular cache on the next request by invoking `$zooapi->resetCache();`.
* You can reset all cached pages by invoking `$zooapi->reset();`.
 
## Good to know ##
* Zoocache requires `PHP 5.3` or newer and (but not necesarily) `zlib` for optional output compression.
* Zoocache is freely licensed under the terms of the **GNU General Public License**.
* You can easily build a storage driver yourself, if the database system you are using is not implemented.
* In `debug mode` you can track all errors with the HTTP response headers, but in production environments it's recommended to turn it off.
* If you want to integrate features like a web statistics tool, you can do that above the line `include 'bootstrap.php;`. It will then be excecuted everytime the page is shown, although you might not see the counter change, because the output is cached.
* If you output anything above the line `include 'bootstrap.php;` this part will be shown several times after some time, increasing with caching of that page.

## See also ##
* Submit any bug or suggestion to the [Issue Tracker](http://github.com/marcelklehr/zoocache/issues)
* Download the latest version [here](https://github.com/marcelklehr/zoocache/downloads)