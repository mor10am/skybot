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
use Skybot\Skype\Reply;

class Ping extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^ping( me)?$/";
	protected $description = "Answers a 'ping' with a 'pong'";

	public function handle($chatmsg, $result)
	{
		return "Yo! Pong to you " . $chatmsg->getSkypeName() . "!";			
	}
}