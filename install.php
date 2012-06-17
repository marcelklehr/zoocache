<?php
namespace Zoo;
include dirname(__FILE__).'/../../autoload.php';
Cache::setUp();
$driver_name = Config::get('driver');
// Install driver
if(Cache::$driver->install() === FALSE)
{
	die("Hmm, something went wrong during the installation process of the $driver_name driver. Please, check your $driver_name driver settings in 'config.php'!");
}
die("Successfully installed $driver_name driver. Ready for caching!");
?>