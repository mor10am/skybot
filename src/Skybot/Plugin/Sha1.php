<?php

namespace Skybot\Plugin;

use Skybot\BasePlugin;
use Skybot\PluginInterface;
use Skybot\Skype\Reply;

class Sha1 extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^sha1( me)?\ (.*)$/";
	protected $description = "Create a SHA1 hash of a string";

	public function handle($chatmsg, $result)
	{
		return sha1($result[2]);
	}
}