<?php

namespace Skybot\Plugin;

use Skybot\BasePlugin;
use Skybot\PluginInterface;

class Ping extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^ping$/";
	protected $description = "Answers a 'ping' with a 'pong'";

	public function handle($result, $message)
	{
		$message->reply("Yo! Pong to you " . $message->getSkypeName() . "!");			

		return true;
	}
}