<?php

$test = "TzoyNToiU2t5Ym90XFNreXBlXEFzeW5jTWVzc2FnZSI6Nzp7czo5OiJtZXNzYWdlaWQiO3M6NDoiMzQ5MCI7czo0OiJib2R5IjtzOjEwOiJjb3VudGFzeW5jIjtzOjk6InRpbWVzdGFtcCI7czoxMDoiMTMzOTQ5MTY0NyI7czo5OiJza3lwZW5hbWUiO047czo2OiJjaGF0aWQiO3M6NDg6IiNtb3J0ZW5fYW11bmRzZW4vJHN5c2Rldl9za3lib3Q7NGE1YjNjNjQxNDVhNjZiZiI7czo2OiJyZXN1bHQiO2E6MTp7aTowO3M6MTA6ImNvdW50YXN5bmMiO31zOjY6InBsdWdpbiI7czoyNDoiU2t5Ym90XFBsdWdpblxDb3VudEFzeW5jIjt9";

$loader = require_once 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Skybot\Skype\AsyncMessage;

try {
	$config = new \Skybot\Config(__DIR__."/config.yml");	

} catch (Exception $e) {
	die($e->getMessage()."\n");
}

$loader->add('Skybot\\Plugin', $config->getPluginDir());

$log = new Logger('async');
$log->pushHandler(new StreamHandler($config->getLogDir()."/".date('Ymd').".log", Logger::DEBUG));

$dic = new \Pimple();
$dic['config'] = $config;
$dic['log'] = $log;

$plugincontainer = new \Skybot\PluginContainer($dic);

//$chatmsg = unserialize(base64_decode($argv[1]));
$chatmsg = unserialize(base64_decode($test));
$chatmsg->dic = $dic;

$class = $chatmsg->plugin;

$plugin = new $class($dic);

$plugincontainer->add($plugin);

$dic['plugincontainer'] = $plugincontainer;

$plugin->handleAsync($chatmsg);
