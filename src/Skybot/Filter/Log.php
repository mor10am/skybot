<?php

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