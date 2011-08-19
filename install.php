<?php
namespace Zoo;
define('ZOOCACHE_VER','1.0');
define('ZOOCACHE_INC', dirname(__FILE__));
include ZOOCACHE_INC.'/driver.php';
include ZOOCACHE_INC.'/cache.php';
include ZOOCACHE_INC.'/config.php';
Cache::option('firstrun', true);
Cache::init();
?>