<?php

namespace Skybot\Plugin;

use Skybot\BasePlugin;
use Skybot\PluginInterface;

class ListPlugins extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^plugins$/";
	protected $description = "List all plugins.";

	public function handle($result, $message)
	{
		$txt = "\r\nAvailable plugins:\r\n\r\n";

		$dic = $message->getDic();

		$plugins = $dic['plugincontainer']->getPlugins();

		$i = 1;
		foreach ($plugins as $plugin) {
			$txt .= $i.") ".$plugin->getDescription()." (".$plugin->getRegexp().")\r\n";
			$i++;
		}

		$message->reply($txt, true);			
	}
}