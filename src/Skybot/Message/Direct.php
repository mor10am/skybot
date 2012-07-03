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

class Direct
{
	private $contactname;
	private $body;
	private $chatid;

	public function __construct($contactname, $chatid, $body)
	{
		$this->contactname = $contactname;
		$this->body = $body;
		$this->chatid = $chatid;
	}

	public function getContactName()
	{
		return $this->contactname;
	}

	public function getChatId()
	{
		return $this->chatid;
	}

	public function getBody()
	{
		return $this->body;
	}
}