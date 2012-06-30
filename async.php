#!/usr/bin/env php
<?php

/*
* This file is part of Skybot
*
* (c) 2012 Morten Amundsen <mor10am@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

$loader = require_once 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Skybot\Skype\AsyncMessage;

if (isset($_SERVER['PWD'])) {
    $basedir = $_SERVER['PWD'];
} else {
    $basedir = __DIR__;
}

try {
	$config = new \Skybot\Config($basedir."/config.yml");

	$storage = new \Skybot\Storage($basedir."/skybot.db");

} catch (Exception $e) {
	die($e->getMessage()."\n");
}

$loader->add('Skybot\\Plugin', $config->getPluginDir());

$log = new Logger('async');
$log->pushHandler(new StreamHandler($config->getLogDir()."/".date('Ymd').".log", Logger::DEBUG));

$dic = new \Pimple();
$dic['config'] = $config;
$dic['log'] = $log;
$dic['storage'] = $storage;

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
