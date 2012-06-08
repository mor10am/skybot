<?php

namespace Skybot\Plugin;

use Skybot\BasePlugin;
use Skybot\PluginInterface;

class Ping extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^ping$/";
	protected $description = "Answers a 'ping' with a 'pong'";

	public function handle($result, $handle)
	{
		$this->reply("Yo! Pong to you " . $handle . "!");			

		return true;
	}
}