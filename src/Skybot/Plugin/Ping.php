<?php

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
		$chatmsg->reply("Yo! Pong to you " . $chatmsg->getSkypeName() . "!");			
	}
}