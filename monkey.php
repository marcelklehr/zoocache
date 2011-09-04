<?php
namespace Zoo;
define('ZOOCACHE_INC', dirname(__FILE__));
include ZOOCACHE_INC.'/zoo.php';

if(isset($_GET['recache']))
{
	$vars = json_decode(file_get_contents('php://input'), true);
	$content = file_get_contents($vars['resource'], false, stream_context_create(array(
				'http' => array(
					'header' => "Referer: $vars[key]\r\n".
								"Connection: Close\r\n",
					'method' => 'post',
					'protocol_version' => 1.1,
					'timeout' => 15
				)
	)));
	Cache::init($vars['resource'])->storeCache($content);
}
?>