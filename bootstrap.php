<?php
namespace Zoo;
define('ZOOCACHE_INC', dirname(__FILE__));
include ZOOCACHE_INC.'/zoo.php';
include ZOOCACHE_INC.'/class/engine.class.php';
Engine::init();
?>