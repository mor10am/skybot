<?php

namespace Skybot;

use Skybot\Skype;
use Skybot\Skype\Message;
use Skybot\Plugin;
use Symfony\Component\Finder\Finder;

class PluginContainer
{
	private $plugins = array();
	private $eventemitter;

	public function __construct($eventemitter, $skype)
	{
		$this->eventemitter = $eventemitter;

		$finder = new Finder();
		$finder->files()->in(__DIR__."/Plugin/")->name("*.php");

		foreach ($finder as $file) {
			$classname = "Skybot\\Plugin\\".basename($file->getFileName(), ".php");

			if (in_array("Skybot\\PluginInterface", class_implements($classname))) {
				$plugin = new $classname($skype);

				if ($plugin instanceof BasePlugin) {
					$this->plugins[] = $plugin;
				}
			} 	
		}

		$plugincontainer = $this;

		$eventemitter->on('skype.message', function(Message $chatmsg) use ($plugincontainer) {

			if (!$chatmsg->isMarked()) {
				foreach ($plugincontainer->getPlugins() as $plugin) {
					try {
						if ($plugin->parse($chatmsg)) break;				
					} catch (\Exception $e) {
						$chatmsg->reply($e->getMessage());
					}
				}
			}	
		});
	}

	public function getPlugins()
	{
		return $this->plugins;
	}
}