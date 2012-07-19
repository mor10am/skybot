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
use Skybot\Message\Chat;

class Ask extends BasePlugin implements PluginInterface
{
	protected $regexp = "/^ask( me)? (.*)$/ms";
	protected $description = "Ask a question.";

	public function handle(Chat $chatmsg)
	{
		if ($chatmsg->isMsgCaptured()) {
			return "You answered: ".$chatmsg->getBody();
		} else {
			$this->captureNext($chatmsg->getContactName());

			$result = $chatmsg->getResult();

			$question = trim($result[2]);
			if (substr($question, -1) != '?') {
				$question .= "?";
			}

			return $question;
		}
	}
}
