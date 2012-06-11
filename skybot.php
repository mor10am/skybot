<?php

if (!extension_loaded('dbus')) {
	die("Skybot requires the DBus extension.\nInstall by doing 'pecl install dbus'\n");
}

$loader = require_once 'vendor/autoload.php';

use Symfony\Component\Finder\Finder;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Skybot\Skype\Message;

try {
	$config = new \Skybot\Config(__DIR__."/config.yml");	

} catch (Exception $e) {
	die($e->getMessage()."\n");
}

$loader->add('Skybot\\Plugin', $config->getPluginDir());

$log = new Logger('skybot');
$log->pushHandler(new StreamHandler($config->getLogDir()."/".date('Ymd').".log", Logger::DEBUG));

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
$finder->files()->in($config->getPluginDir())->name("*.php");

foreach ($finder as $file) {
	$classname = "Skybot\\Plugin\\".basename($file->getFileName(), ".php");

	$implements = class_implements($classname);

	if (!$implements) continue;

	if (in_array("Skybot\\PluginInterface", $implements)) {
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
