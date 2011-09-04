# Zoocache #
Zoocache is the intelligent output caching tool you wished to have ever since for your PHP application.

## Features ##
+ GZip compression
+ Different storage drivers (currently file, mysql - easily extendable)
+ Caching blacklist
+ Adjust the storage key generation
+ API to reset the cache from within your application
+ High performance due to background recaching

## Set-up ##
1. Download the package and copy the `/zoocache` folder into your project's directory.
2. Alter the options (located in `options.php` inside the `/zoocache` directory) to your wishes and choose a caching driver.
3. Now, open `install.php` with your browser. You should see a short message, which states that the chosen driver was installed successfully. If not, check your driver settings and any other possible resource.
4. When everything is fine so far, load `test.php` with your browser, you can check, whether zoocache is working.
5. Include the `bootstrap.php` file at the **top** of all files you want to cache. Any output above this include statement may lead to unpredictable results!
 
## Good to know ##
* Zoocache requires `PHP 5.3` or newer and (but not necesarily) `zlib` for optional output compression.
* Zoocache is freely licensed under the terms of the **GNU General Public License**.
* You can easily build a storage driver yourself, if the database system you are using is not implemented.  
* It's recommended to turn off error reporting when using gzip output compression, because non-gzipped PHP errors mess up the gzip encoding.
* In `debug mode` you can track all errors with the HTTP response headers, but in production environments it's recommended to turn it off.

## See also ##
* Submit any bug or suggestion to the [Issue Tracker](http://github.com/marcelklehr/zoocache/issues)
* Download the latest version [here](https://github.com/marcelklehr/zoocache/downloads)