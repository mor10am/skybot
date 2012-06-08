<?php

namespace Skybot;

use Skybot\Skype;
use Skybot\Skype\Message;

abstract class BasePlugin
{
	protected $skype;
	protected $regexp;
	protected $description;
	protected $currentmsg;

	public function __construct(Skype $skype = null)
	{
		$this->skype = $skype;
	}

	public function parse(Message $message)
	{		
		if (!$this->regexp) return false;
		if (!$matches = preg_match($this->regexp, $message->getBody(), $result)) return false;
		
		$this->currentmsg = $message;
		
		$res = $this->handle($result);

		$this->currentmsg = null;

		return $res;
	}	

	protected function reply($msg)
	{
		if ($this->currentmsg) {
			$this->currentmsg->reply($msg);
		}
	}

	public function getSkype()
	{
		return $this->skype;
	}

	public function getRegExp()
	{
		return $this->regexp;
	}

	public function getDescription()
	{
		return $this->description;
	}
}