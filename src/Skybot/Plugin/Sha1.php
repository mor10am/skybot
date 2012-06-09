<?php

namespace Skybot\Plugin;

use Skybot\BasePlugin;
use Skybot\PluginInterface;

class Sha1 extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^sha1( me)?\ (.*)$/";
	protected $description = "Create a SHA1 hash of a string";

	public function handle($result, $message)
	{
		if (isset($result[1]) and trim($result[1]) == 'me') {
			$dm = true;
		} else {
			$dm = false;
		}

		$message->reply(sha1($result[2]), $dm);			
	}
}