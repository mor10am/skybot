<?php

/*
* This file is part of Skybot
*
* (c) 2012 Morten Amundsen <mor10am@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Skybot\Skype;

use Skybot\Skype\Message;
use Skybot\Skype\DirectMessage;

class Reply
{
	private $body;
	private $dm = false;
	private $chatmsg;
	private $skypename;
	private $chatid;

	public function __construct($chatmsg, $body, $dm = false)
	{
		$this->chatmsg = $chatmsg;
		$this->chatid = $chatmsg->getChatId();
		$this->skypename = $chatmsg->getSkypeName();
		$this->body = $body;
		$this->dm = $dm;
	}

	public function setChatId($chatid)
	{
		$this->chatid = $chatid;
	}

	public function getChatId()
	{
		return $this->chatid;
	}

	public function getChatMsg()
	{
		return $this->chatmsg;
	}

	public function getSkypeName()
	{
		return $this->skypename;
	}

	public function getBody()
	{
		return $this->body;
	}

	public function isDM()
	{
		return $this->dm;
	}

	public function createDirectMessage()
	{
		return new DirectMessage($this->skypename, $this->body);
	}
}