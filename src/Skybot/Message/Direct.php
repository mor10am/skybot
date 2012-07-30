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

use Skybot\User;

class Direct
{
	private $user;
	private $body;
	private $chatid;

	public function __construct(User $user, $chatid, $body)
	{
		$this->user = $user;
		$this->body = $body;
		$this->chatid = $chatid;
	}

	public function getUser()
	{
		return $this->user;
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