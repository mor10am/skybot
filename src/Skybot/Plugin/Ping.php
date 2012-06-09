<?php

namespace Skybot\Plugin;

use Skybot\BasePlugin;
use Skybot\PluginInterface;

class Ping extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^ping( me)?$/";
	protected $description = "Answers a 'ping' with a 'pong'";

	public function handle($result, $message)
	{
		if (isset($result[1]) and trim($result[1]) == 'me') {
			$dm = true;
		} else {
			$dm = false;
		}

		$message->reply("Yo! Pong to you " . $message->getSkypeName() . "!", $dm);			
	}
}