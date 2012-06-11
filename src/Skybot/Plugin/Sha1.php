<?php

namespace Skybot\Plugin;

use Skybot\BasePlugin;
use Skybot\PluginInterface;
use Skybot\Skype\Reply;

class Sha1 extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^sha1( me)?\ (.*)$/";
	protected $description = "Create a SHA1 hash of a string";

	public function handle($result, $chatmsg, $dm = false)
	{
		return new Reply($chatmsg, sha1($result[2]), $dm);			
	}
}