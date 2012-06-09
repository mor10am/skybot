<?php

namespace Skybot;

use Skybot\Skype;
use Skybot\Skype\Message;
use Skybot\Plugin;

class PluginContainer
{
	private $plugins = array();
	private $dic;	

	public function __construct(\Pimple $dic)
	{
		$this->dic = $dic;

		if (isset($dic['eventemitter'])) {
			$dic['eventemitter']->on('skype.message', function(Message $chatmsg) use ($dic) {

				$plugincontainer = $dic['plugincontainer'];

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