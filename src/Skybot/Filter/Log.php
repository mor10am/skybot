<?php

/*
* This file is part of Skybot
*
* (c) 2012 Morten Amundsen <mor10am@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Skybot\Filter;

use Skybot\BaseFilter;
use Skybot\FilterInterface;
use Skybot\Skype\Message;

class Log extends BaseFilter implements FilterInterface
{
	public $pri = 1;
	public $pos = self::BEFORE_PLUGINS;
	public $description = "Log Skype message to log file";

	public function handle(Message $chatmsg)
	{
		if ($dic = $chatmsg->getDic() and isset($dic['log'])) {
			$dic['log']->addDebug($chatmsg->getSkypeName() . ": " . $chatmsg->getBody());
		}

		return $chatmsg;
	}
}