<?php

namespace Skybot\Plugin;

use Skybot\BasePlugin;
use Skybot\PluginInterface;
use Skybot\Skype\Reply;
use Skybot\Skype\AsyncMessage;

class CountAsync extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^countasync$/";
	protected $description = "Count for 10 seconds";
	protected $async = true;

	public function handle($result, $chatmsg, $dm = false)
	{		
	}

	public function handleAsync(AsyncMessage $chatmsg)
	{		
		for ($i = 1;$i < 10;$i++) {
			$chatmsg->reply(new Reply($chatmsg, "async count $i"));
			sleep(1);
		}
	}
}