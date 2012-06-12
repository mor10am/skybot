#!/usr/bin/env php
<?php

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

if (isset($argv[1])) {
	$data = $argv[1];
} else {
	die("No data provided.\n");
}

$chatmsg = unserialize(base64_decode($data));
$chatmsg->dic = $dic;

$class = $chatmsg->plugin;

$plugin = new $class($dic);

$plugincontainer->add($plugin);

$dic['plugincontainer'] = $plugincontainer;

$plugin->handleAsync($chatmsg);
