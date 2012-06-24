<?php

/*
* This file is part of Skybot
*
* (c) 2012 Morten Amundsen <mor10am@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

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

	public function handle($chatmsg, $result)
	{		
	}

	public function handleAsync(AsyncMessage $chatmsg)
	{		
		for ($i = 1;$i < 10;$i++) {
			$chatmsg->reply("async count $i");
			sleep(1);
		}
	}
}