# Zoocache #
Zoocache is the intelligent output caching tool you wished to have ever since for your PHP application.

## Features ##
+ GZip compression
+ Different storage drivers (currently file, mysql - easily extendable)
+ Blacklist (RegEx)
+ Options for storage key generation
+ API to reset the cache from within your application
+ High performance, due to non-blocking caching

## Set-up ##
1. Download the package and copy the `/zoocache` folder into your project's directory.
2. Alter the options (located in `/zoocache/options.php`) to your wishes and choose a caching driver.
3. Now, load `install.php` with your browser. You should see a short message, which states that the chosen driver was installed successfully. If not, check your driver settings and any other possible resource.
4. When everything is fine so far, load `test.php` with your browser to check whether zoocache is working.
5. Include the `bootstrap.php` file at the **top** of all files you want to cache. Any output above this include statement may lead to unpredictable results! ALternatively you can also add an auto_prepend driective in your `.htaccess` file: `php_value auto_prepend_file /full/path/to/bootstrap.php` If you do, be shure to add `zoocache/monkey.php` to your blacklist.
 
## Good to know ##
* Zoocache requires `PHP 5.3` or newer and (but not necesarily) `zlib` for optional output compression.
* Zoocache is freely licensed under the terms of the **GNU General Public License**.
* You can easily build a storage driver yourself, if the database system you are using is not implemented.  
* It's recommended to turn off error reporting when using gzip output compression, because non-gzipped PHP errors mess up the gzip encoding.
* In `debug mode` you can track all errors with the HTTP response headers, but in production environments it's recommended to turn it off.
* If you want to integrate something like a visit counter, you can do that above `include 'bootstrap.php;`. It will then be excecuted everytime the pae is shown, although you might not see the counter change, because the output is cached. If you output anything above the bootstrap this will be shown several times after some time, increasing with recaching of that page.

## How it works ##
*'There are loads of caching tools out there, what's so special about this one?'* you might ask, which is a reasonable question. Actually, most of these caching tools use the PHP output buffer, to cache new contents.
The trouble is, everytime the snapshot of a file expires, the very next user will have to wait much longer than usual, even longer than without caching enabled. Why is that? Well, at first, the whole application has to process the request, which takes a while for bigger applications (and that is, why you wanted a caching tool, anyway) and secondly, the output has to be stored in the cache by the caching tool, which can also take a while, depending on the storage system used.
**Zoocache**, however, does this in a way which is probably described best as *non-blocking*: Users never have to wait for the application to process the request, just for the purpose of easier recaching. When a cached snapshot expires, it displays it once more to the user and simultaneously sends a request to another PHP script (we call this one the *monkey*) which will then handle the recaching - indepently from the calling script.

## See also ##
* Submit any bug or suggestion to the [Issue Tracker](http://github.com/marcelklehr/zoocache/issues)
* Download the latest version [here](https://github.com/marcelklehr/zoocache/downloads)