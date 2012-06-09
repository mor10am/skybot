<?php

$loader = require_once 'vendor/autoload.php';

use Symfony\Component\Finder\Finder;

try {
	$config = new \Skybot\Config(__DIR__."/config.yml");	

} catch (Exception $e) {
	die($e->getMessage()."\n");
}

$eventemitter = new Evenement\EventEmitter();

$skype = new \Skybot\Skype($config, $eventemitter);

$plugins = new \Skybot\PluginContainer($config, $eventemitter);

$finder = new Finder();
$finder->files()->in($config->getPluginDir())->name("*.php");

foreach ($finder as $file) {
	$classname = "Skybot\\Plugin\\".basename($file->getFileName(), ".php");

	if (in_array("Skybot\\PluginInterface", class_implements($classname))) {
		$plugin = new $classname($skype);

		if ($plugin instanceof BasePlugin) {
			$plugins->add($plugin);
		}
	} 	
}

$skybot = new \Skybot($skype, $plugins);
$skybot->run();
