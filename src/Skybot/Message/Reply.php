<?php

/*
* This file is part of Skybot
*
* (c) 2012 Morten Amundsen <mor10am@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Skybot\Message;

use Skybot\Message\Chat;
use Skybot\Message\Direct;

class Reply
{
	private $body;
	private $dm = false;
	private $chatmsg;
	private $contactname;
	private $chatid;

	public function __construct($chatmsg, $body, $dm = false)
	{
		$this->chatmsg = $chatmsg;
		$this->chatid = $chatmsg->getChatId();
		$this->contactname = $chatmsg->getContactName();
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

	public function getContactName()
	{
		return $this->contactname;
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
		return new Direct($this->contactname, $this->chatid, $this->body);
	}
}