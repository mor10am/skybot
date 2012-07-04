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
use Skybot\Message\Async;

if (isset($_SERVER['PWD'])) {
	$basedir = $_SERVER['PWD'];
} else {
	$basedir = __DIR__;
}

try {
	$config = new \Skybot\Config($basedir."/config.yml");

} catch (Exception $e) {
	die($e->getMessage()."\n");
}

$loader->add('Skybot\\Plugin', $config->getPluginDir());

$log = new Logger('async');
$log->pushHandler(new StreamHandler($config->getLogDir()."/skybot.log", Logger::DEBUG));

$driver = new \Skybot\Driver\Dummy();

$skybot = new \Skybot\Main($driver, $config, $log);

$plugincontainer = new \Skybot\PluginContainer($skybot);

$skybot->setPluginContainer($plugincontainer);

if (isset($argv[1])) {
	$data = $argv[1];
} else {
	die("No data provided.\n");
}

$chatmsg = unserialize(base64_decode($data));
$chatmsg->setSkybot($skybot);

$class = $chatmsg->plugin;

$plugin = new $class($skybot);

$plugincontainer->add($plugin);

$plugin->handleAsync($chatmsg);
