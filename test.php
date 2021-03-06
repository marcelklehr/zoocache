<?php
/**
 * As you can see, using Zoocache on your site is very easy:
 */
Zoo\Engine::init(Zoo\USE_PATH|Zoo\USE_GETVARS)
    ->compress()
    ->expireIn(20)
    ->setBlacklist(array(
    '~\?nocache$~'
    ))
->run();

/**
 * Let's do something that takes really long, so we can see the difference
 */
sleep(4);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Zoocache <?php print Zoo\Cache::VERSION; ?> - Test page</title>
<style type="text/css">
body{color:#000000;background-color:#FFFFFF;padding-left:1em;}
a:link{color:#060;}
blockquote{margin-left:0;padding:0.5em;padding-left:2em;border-left:1px dotted #000;background-color:#ff9;}
em{font-size:small;}
strong{color:#600;}
h1{margin-bottom:0.75em;}
h1 span{color:#050}
</style>
</head>
<body>
<h1><span>Zoocache</span> Test page</h1>
<blockquote><pre>UNIX timestamp: <?php print time(); ?></pre></blockquote>
<p>If everything is working correctly now, you should see the same digits above after reloading the page. That means it has been cached!<br />
Also try <a href="<?php print $_SERVER['PHP_SELF']; ?>?nocache">?nocache</a>, which is on the caching blacklist. Therefore, when watching that page, you should see the digits change on reload and, by the way, the page should take much longer to load.</p>
<?php if(Zoo\Config::get('debug')){ ?><p><strong>Zoocache is running in debug mode!</strong><br/>It's recommended to turn off debug mode in a production environment, since debug messages can be read by everybody in the HTTP headers.</p><?php } ?>
<p></p>
<p><em>You are running Zoocache/<?php print Zoo\Cache::VERSION; ?>; driver:<?php print Zoo\Config::get('driver'); ?>; filters: <?php print join(', ', Zoo\Config::get('filters')); ?>; debug mode: <?php print (Zoo\Config::get('debug')) ? 'on' : 'off'; ?></em></p>
</body>
</html>