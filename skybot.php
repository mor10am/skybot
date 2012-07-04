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
use Symfony\Component\EventDispatcher\Event;

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

$driver = new \Skybot\Driver\Skype();
$driver->initialize(array(
		'appname'	=> 'SKYBOT',
		'protocol'	=> 7
	));

$skybot = new \Skybot\Main($driver, $config, $log);

$plugincontainer = new \Skybot\PluginContainer($skybot);

$skybot->setPluginContainer($plugincontainer);

$skybot->addListener('skybot.message', function(Event $chatmsg) use ($plugincontainer) {
	if (!$chatmsg->isMarked()) {
		try {
			$plugincontainer->parseMessage($chatmsg);
		} catch (Exception $e) {

		}
	}
});

try {
	$plugincontainer->loadPlugins(array($config->getPluginDir(), __DIR__."/src/Skybot/Plugin/"));
	$plugincontainer->loadFilters(array($config->getFilterDir(), __DIR__."/src/Skybot/Filter/"));

	do {
		$skybot->handleChatMessages();

		usleep(500000);

	} while(true);

} catch (Exception $e) {
	$log->addError($e->getMessage());
	die($e->getMessage());
}
