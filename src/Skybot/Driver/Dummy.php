<?php

/*
* This file is part of Skybot
*
* (c) 2012 Morten Amundsen <mor10am@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Skybot\Driver;

use Skybot\DriverInterface;

use Skybot\Main;
use Skybot\Message\Chat;
use Skybot\Message\Direct;
use Skybot\Message\Reply;
use Skybot\User;

class Dummy implements DriverInterface
{
	public function __construct(\Monolog\Logger $log)
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

	public function getRecentMessagesForChat($chatid, Main $chatmsg)
	{
		return array();
	}

	public function getMessageProperties(Chat $chatmsg)
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

	public function createChatWith(User $user)
	{
	}

	public function markSeen(Chat $chatmsg)
	{
	}

	public function getContacts()
	{
		return array();
	}
}