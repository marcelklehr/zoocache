<?php
define('ZOOCACHE_INC', dirname(__FILE__));
include ZOOCACHE_INC.'/zoo.php';

Zoo_Cache::init();
$driver_name = Zoo_Config::get('driver');
// Install driver
if(Zoo_Cache::$driver->install() === FALSE)
{
	die("Hmm, something went wrong during the installation process of the $driver_name driver. Please, check your $driver_name driver settings in 'config.php'!");
}
die("Successfully installed $driver_name driver. Ready for caching!");
?>