<?php

namespace Skybot;

use Skybot\Skype;
use Skybot\Plugin;
use Symfony\Component\Finder\Finder;

class PluginContainer
{
	private $plugins = array();

	public function __construct(Skype $skype = null)
	{
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
	}

	public function handle($messages)
	{
		if (!count($messages)) return false;

		foreach ($messages as $chatmsg) {
			if ($chatmsg->isMarked()) continue;

			foreach ($this->plugins as $plugin) {
				try {
					if ($plugin->parse($chatmsg)) break;				
				} catch (\Exception $e) {
					$chatmsg->reply($e->getMessage());
				}
			}

			$chatmsg->mark();
		}
	}
}