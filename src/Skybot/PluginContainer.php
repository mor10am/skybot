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

				if (!$chatmsg->isMarked()) {

					$plugincontainer = $dic['plugincontainer'];

					if (preg_match("/^plugins$/", $chatmsg->getBody())) {
						return $plugincontainer->builtinListPlugins($chatmsg);						
					}

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

	public function builtinListPlugins(Message $chatmsg)
	{
		$dic = $chatmsg->getDic();
		$plugins = $dic['plugincontainer']->getPlugins();

		$txt = "\r\nAvailable plugins:\r\n\r\n";

		$i = 1;

		foreach ($plugins as $plugin) {
			$txt .= $i.") ".$plugin->getDescription()." (".$plugin->getRegexp().")\r\n";
			$i++;
		}

		$chatmsg->reply($txt, true);					
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