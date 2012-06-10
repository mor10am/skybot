<?php

namespace Skybot;

use Skybot\Skype;
use Skybot\Skype\Message;
use Skybot\Skype\Reply;

abstract class BasePlugin
{
	protected $dic;
	protected $regexp;
	protected $description;
	protected $result;

	public function __construct(\Pimple $dic = null)
	{
		$this->dic = $dic;
	}

	public function parse(Message $chatmsg)
	{		
		if (!$this->regexp) return false;
		if (!$matches = preg_match($this->regexp, $chatmsg->getBody(), $result)) return false;
		
		$this->result = $result;

		if ($this->dic['log']) {
			$this->dic['log']->addInfo($chatmsg->getSkypeName()." to Skybot : ".$chatmsg->getBody());
		}

		return $this->handle($result, $chatmsg);
	}	

	public function getResult()
	{
		return $this->result;
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