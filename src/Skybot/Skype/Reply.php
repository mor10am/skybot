<?php

namespace Skybot\Skype;

use Skybot\Skype\Message;
use Skybot\Skype\DirectMessage;

class Reply
{
	private $body;
	private $dm = false;
	private $chatmsg;
	private $skypename;

	public function __construct(Message $chatmsg, $body, $dm = false)
	{
		$this->chatmsg = $chatmsg;
		$this->skypename = $chatmsg->getSkypeName();
		$this->body = $body;
		$this->dm = $dm;
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