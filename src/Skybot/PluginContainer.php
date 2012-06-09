<?php

namespace Skybot;

use Skybot\Skype;
use Skybot\Skype\Message;
use Skybot\Plugin;

class PluginContainer
{
	private $plugins = array();
	private $config;
	private $eventemitter;

	public function __construct($config, $eventemitter)
	{
		$this->config = $config;
		$this->eventemitter = $eventemitter;

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

	public function add(PluginInterface $plugin)
	{
		$this->plugins[] = $plugin;
	}

	public function getPlugins()
	{
		return $this->plugins;
	}
}