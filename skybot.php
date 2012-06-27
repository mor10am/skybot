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

if (!extension_loaded('dbus')) {
	die("Skybot requires the DBus extension.\nInstall by doing 'pecl install dbus'\n");
}

$loader = require_once 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Skybot\Skype\Message;

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

$config->bin_dir = $basedir;

if ($config->getPluginDir()) {
	$loader->add('Skybot\\Plugin', $config->getPluginDir());
}

$log = new Logger('skybot');
$log->pushHandler(new StreamHandler($config->getLogDir()."/skybot.log", Logger::DEBUG));

$dic = new \Pimple();
$dic['config'] = $config;
$dic['log'] = $log;

$skype = new \Skybot\Skype($dic);

$plugincontainer = new \Skybot\PluginContainer($dic);

$dic['skype'] = $skype;
$dic['plugincontainer'] = $plugincontainer;

$skype->on('skype.message', function(Message $chatmsg) use ($plugincontainer) {
	if (!$chatmsg->isMarked()) {
		$plugincontainer->parseMessage($chatmsg);
	}	
});

try {
	$plugincontainer->loadPlugins(array($config->getPluginDir(), __DIR__."/src/Skybot/Plugin/"));
	$plugincontainer->loadFilters(array($config->getFilterDir(), __DIR__."/src/Skybot/Filter/"));

	$skybot = new \Skybot($dic);
	$skybot->run();
} catch (Exception $e) {
	$log->addError($e->getMessage());
	die($e->getMessage());
}
