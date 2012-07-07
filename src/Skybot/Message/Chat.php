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

use Skybot\Message\Reply;
use Skybot\Message\Async;
use Symfony\Component\EventDispatcher\Event;

class Chat extends Event
{
	private $messageid;
	private $body;
	private $timestamp;
	private $contactname;
	private $dispname;
	private $chatid;
	private $marked = false;
	private $dm = false;
	private $skybot;
	private $replybody;
	private $internal = false;

	public function __construct($msgid = null, $chatid = null, \Skybot\Main $skybot = null)
	{
		$this->messageid = $msgid;
		$this->chatid = $chatid;
		$this->skybot = $skybot;

		if ($msgid and $skybot) {
			$properties = $skybot->getDriver()->getMessageProperties($this);
			$this->setContactName($properties['contactname']);
			$this->setDispName($properties['displayname']);
			$this->setBody($properties['body']);
			$this->timestamp = $properties['timestamp'];
		}
	}

	public function setChatId($chatid)
	{
		$this->chatid = $chatid;
	}

	public function getMessageId()
	{
		return $this->messageid;
	}

	public function setInternal()
	{
		$this->internal = true;
	}

	public function isInternal()
	{
		return $this->internal;
	}

	public function setDM()
	{
		$this->dm = true;
	}

	public function isDM()
	{
		return $this->dm;
	}

	public function setSkybot(\Skybot\Main $skybot)
	{
		$this->skybot = $skybot;
	}

	public function getSkybot()
	{
		return $this->skybot;
	}

	public function getChatId()
	{
		return $this->chatid;
	}

	public function setBody($msg)
	{
		$this->body = $msg;
	}

	public function setContactName($contactname)
	{
		$this->contactname = $contactname;
	}

	public function getContactName()
	{
		return $this->contactname;
	}

	public function setDispName($name)
	{
		$this->dispname = $name;
	}

	public function getDispName()
	{
		if (!$this->dispname) return $this->getContactName();
		return $this->dispname;
	}

	public function getTimestamp()
	{
		return $this->timestamp;
	}

	public function isEmpty()
	{
		return (strlen($this->body) == 0);
	}

	public function getBody()
	{
		return $this->body;
	}

	public function getReplyBody()
	{
		return $this->replybody;
	}

	public function mark()
	{
		$this->marked = true;

		if ($this->messageid and $this->skybot) {
			$this->skybot->getDriver()->markSeen($this);
		}
	}

	public function isMarked()
	{
		return $this->marked;
	}

	public function reply($txt)
	{
		$this->replybody = $txt;

		if ($this->skybot) {
			if ($txt instanceof Reply) {
				$this->skybot->reply($txt);
			} else {
				$this->skybot->reply(new Reply($this, $txt, $this->dm));
			}
		} else {
			echo $txt."\n";
		}

		return true;
	}

	public function createAsyncMessage()
	{
		$msg = new Async();
		$msg->body = $this->body;
		$msg->chatid = $this->chatid;
		$msg->messageid = $this->messageid;
		$msg->timestamp = $this->timestamp;
		$msg->contactname = $this->contactname;
		$msg->skybot = $this->skybot;

		return $msg;
	}
}