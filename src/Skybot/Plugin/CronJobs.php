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

class CronJobs extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^cron$/";
	protected $description = "List all cronjobs.";

	public function handle(Chat $chatmsg, $result)
	{
		if (!$this->skybot) return "No Skybot registered";

		$cronjobs = $this->skybot->getCronjobs();

		if (!count($cronjobs)) return "No cronjobs registered.";

		$txt = "List of registered CRON jobs:\n\n";

		foreach ($cronjobs as $cronjob) {
			$txt .= $cronjob->getExpression()." [".get_class($cronjob)."] ".$cronjob->getDescription()."\n\r";
		}

		return $txt;
	}
}
