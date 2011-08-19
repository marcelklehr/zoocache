# Zoocache #
Zoocache is an intelligent output caching tool for PHP applications.

## Features ##
+ GZip compression
+ Different storage drivers (file, mysql - easily extendable)
+ Caching blacklist
+ Options for generating storage key (you can use domain, get vars, post vars and cookies for storing)
+ API to reset cache of a specific file/entity (not fully implemented yet)
+ Doesn't blow up your webspace (<15KB file size)

## Set-up ##
1. Download the package and copy the /zoocache folder into your project's directory.
2. Alter the options (located in `config.php` inside the `/zoocache` directory) to your wishes and choose a caching driver.
3. Now, open `install.php` with your browser. You should see a short message, which states that the chosen driver was installed successfully. If not, check your driver settings and any other possible resource.
4. When everything is fine so far, load `test.php` with your browser, you can check, whether zoocache is working.
5. Include the `bootstrap.php` file at the **top** of all files you want to cache. Any output above this include statement will **not** be displayed.
6. Waiting for another step? That's all! Easy, huh?
 
## Good to know ##
* Zoocache requires `PHP 5.3` or higher and (but not necesarily) `zlib` for optional output compression.
* Zoocache is freely licensed under the terms of the **GNU General Public License**.
* You can easily build a storage driver yourself, if the database system you are using is not implemented.  
* It's recommended to turn off error reporting when using gzip output compression, because non-gzipped PHP errors mess up the gzip encoding.
* In debug mode you can track all errors with the HTTP response headers, but in production environments it's recommended to turn it off.