<?php

namespace Skybot\Plugin;

use Skybot\BasePlugin;
use Skybot\PluginInterface;
use Skybot\Skype\Reply;

class EchoString extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^echo( me)? (.*)$/";
	protected $description = "Echo back the same text.";

	public function handle($chatmsg, $result)
	{
		return $result[2];			
	}
}
