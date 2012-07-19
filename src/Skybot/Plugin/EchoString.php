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

class EchoString extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^echo( me)? (.*)$/ms";
	protected $description = "Echo back the same text.";

	public function handle(Chat $chatmsg)
	{
		$result = $chatmsg->getResult();

		return $result[2];
	}
}
