<?php

namespace Skybot\Driver;

use Skybot\DriverInterface;

class Dummy implements DriverInterface
{
	public function __construct()
	{
	}

	public function initialize($params = array())
	{
	}

	public function isContact($name)
	{
		return true;
	}

	public function refuseCalls()
	{
	}

	public function getRecentChats()
	{
		return array();
	}

	public function getMissedChats()
	{
		return array();
	}

	public function getRecentMessagesForChat($chatid)
	{
		return array();
	}

	public function getMessageProperties($msgid)
	{
		return array();
	}

	public function getChatProperty($chatid, $property)
	{
		return '';
	}

	public function sendDirectMessage(Direct $dm)
	{
	}

	public function sendReply(Reply $reply)
	{
	}

	public function createChatWith($contactname)
	{
	}

	public function markSeen(Chat $chatmsg)
	{
	}
}