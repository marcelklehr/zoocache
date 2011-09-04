<?php
namespace Zoo;
define('ZOOCACHE_INC', dirname(__FILE__));
include ZOOCACHE_INC.'/zoo.php';

Cache::init();
$driver = Config::get('driver');
// Install driver
if(Cache::$driver->install() === FALSE)
{
	die("Hmm, something went wrong during the installation process of the $driver driver. Please, check your $driver. driver settings in 'config.php'!");
}
die("Successfully installed $driver driver. Ready for caching!");
?>