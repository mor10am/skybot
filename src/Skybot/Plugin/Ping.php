<?php

namespace Skybot\Plugin;

use Skybot\BasePlugin;
use Skybot\PluginInterface;
use Skybot\Skype\Reply;

class Ping extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^ping( me)?$/";
	protected $description = "Answers a 'ping' with a 'pong'";

	public function handle($result, $chatmsg)
	{
		if (isset($result[1]) and trim($result[1]) == 'me') {
			$dm = true;
		} else {
			$dm = false;
		}

		return new Reply($chatmsg, "Yo! Pong to you " . $chatmsg->getSkypeName() . "!", $dm);			
	}
}