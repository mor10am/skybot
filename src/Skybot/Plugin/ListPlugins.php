<?php

/*
* This file is part of Skybot
*
* (c) 2012 Morten Amundsen <mor10am@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Skybot\Plugin;

use Skybot\BasePlugin;
use Skybot\PluginInterface;
use Skybot\Message\Chat;

class ListPlugins extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^plugins$/";
	protected $description = "List all plugins.";

	public function handle(Chat $chatmsg, $result)
	{

		if (!$this->skybot) return "No Skybot registered";
		if (!$this->skybot->getPluginContainer()) return "No plugins registered.";

		$plugins = $this->skybot->getPluginContainer()->getPlugins();

		$i = 1;
		$txt = "\r\nAvailable plugins:\r\n\r\n";

		foreach ($plugins as $plugin) {
			$txt .= $i.") ".$plugin->getDescription()." (".$plugin->getRegexp().")\r\n";
			$i++;
		}

		return $txt;
	}
}
