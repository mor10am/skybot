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

class Health extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^health( me)?$/";
	protected $description = "Get health statistics of Skybot.";

	public function handle(Chat $chatmsg, $result)
	{
		if (!$this->skybot) {
			return "No Skybot registered";
		}

		$uptime = '???';
		$msgserved = '0';

		$uptime = round((time()-$this->skybot->getStartupTime()) / 60);
		$msgserved = $this->skybot->getMessagesServed();

		$mem = round(memory_get_usage(true) / (1024*1024), 2);
		$peak = round(memory_get_peak_usage(true) / (1024*1024), 2);

		$txt = "Skybot healthcheck:\n\n";
		$txt .= "Uptime: ". $uptime . " minutes\n";
		$txt .= "Memory usage: ".$mem." Mb\n";
		$txt .= "Memory peak usage: ".$peak." Mb\n";
		$txt .= "Messages served: ".$msgserved."\n";

		return $txt;
	}
}
