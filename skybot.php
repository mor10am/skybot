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

use Symfony\Component\Finder\Finder;
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

$loader->add('Skybot\\Plugin', $config->getPluginDir());

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

$finder = new Finder();

$plugindirs = array($config->getPluginDir(), __DIR__."/src/Skybot/Plugin/");

foreach ($plugindirs as $dir) {

	$finder->files()->in($dir)->name("*.php");

	foreach ($finder as $file) {
		require_once $file;

		$classname = "\\Skybot\\Plugin\\".basename($file->getFileName(), ".php");

		$implements = class_implements($classname);

		if (!$implements) continue;

		if (in_array("Skybot\\PluginInterface", $implements)) {
			$plugin = new $classname($dic);

			if ($plugin instanceof \Skybot\BasePlugin) {
				if ($plugincontainer->addPlugin($plugin)) {
					$dic['log']->addDebug("Added plugin $classname : ".$plugin->getDescription());
				}
			} else {
				die("$classname is not instance of Skybot\\BasePlugin\n");
			}
		} else {
			die("$classname is does not implement Skybot\\PluginInterface\n");
		}
	}
}

$finder = new Finder();

$filterdirs = array($config->getFilterDir(), __DIR__."/src/Skybot/Filter/");

foreach ($filterdirs as $dir) {

	try {
		$finder->files()->in($dir)->name("*.php");

		foreach ($finder as $file) {
			require_once $file;

			$classname = "\\Skybot\\Filter\\".basename($file->getFileName(), ".php");

			$implements = class_implements($classname);

			if (!$implements) continue;

			if (in_array("Skybot\\FilterInterface", $implements)) {
				$filter = new $classname($dic);

				if ($filter instanceof \Skybot\BaseFilter) {
					if ($plugincontainer->addFilter($filter)) {
						$dic['log']->addDebug("Added filter $classname : ".$filter->getDescription());
					}
				} else {
					die("$classname is not instance of Skybot\\BaseFilter\n");
				}
			} else {
				die("$classname is does not implement Skybot\\FilterInterface\n");
			}
		}
	} catch (InvalidArgumentException $e) {

	}
}

$skybot = new \Skybot($dic);
$skybot->run();
