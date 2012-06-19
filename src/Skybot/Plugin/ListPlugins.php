<?php

namespace Skybot\Plugin;

use Skybot\BasePlugin;
use Skybot\PluginInterface;
use Skybot\Skype\Reply;

class ListPlugins extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^plugins$/";
	protected $description = "List all plugins.";

	public function handle($chatmsg, $result)
	{
		$dic = $chatmsg->getDic();
		
		if (!$dic) return false;
		if (!isset($dic['plugincontainer'])) return false;

		$plugins = $dic['plugincontainer']->getPlugins();

		$i = 1;
		$txt = "\r\nAvailable plugins:\r\n\r\n";

		foreach ($plugins as $plugin) {
			$txt .= $i.") ".$plugin->getDescription()." (".$plugin->getRegexp().")\r\n";
			$i++;
		}

		return $txt;		
	}
}