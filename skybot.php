<?php

$loader = require_once 'vendor/autoload.php';

use Symfony\Component\Finder\Finder;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

try {
	$config = new \Skybot\Config(__DIR__."/config.yml");	

} catch (Exception $e) {
	die($e->getMessage()."\n");
}

$log = new Logger('skybot');
$log->pushHandler(new StreamHandler($config->getLogDir()."/".date('Ymd').".log", Logger::DEBUG));

$dic = new \Pimple();
$dic['eventemitter'] = $dic->share(function($c) { return new Evenement\EventEmitter(); });
$dic['config'] = $config;
$dic['log'] = $log;

$skype = new \Skybot\Skype($dic);

$plugincontainer = new \Skybot\PluginContainer($dic);

$dic['skype'] = $skype;
$dic['plugincontainer'] = $plugincontainer;

$finder = new Finder();
$finder->files()->in($config->getPluginDir())->name("*.php");

foreach ($finder as $file) {
	$classname = "Skybot\\Plugin\\".basename($file->getFileName(), ".php");

	if (in_array("Skybot\\PluginInterface", class_implements($classname))) {
		$plugin = new $classname($dic);

		if ($plugin instanceof \Skybot\BasePlugin) {
			$plugincontainer->add($plugin);
			$dic['log']->addDebug("Added plugin $classname : ".$plugin->getDescription());
		} else {
			die("$classname is not instance of Skybot\\BasePlugin\n");
		}
	} else {
		die("$classname is does not implement Skybot\\PluginInterface\n");
	}
}

$skybot = new \Skybot($dic);
$skybot->run();
