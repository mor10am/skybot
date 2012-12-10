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

class Skype implements DriverInterface
{
	private $dbus;
	private $proxy;
	private $log;
	private $calls = array();
	private $messages = array();
	private $users = array();

	public function __construct(\Monolog\Logger $log)
	{
		$this->log = $log;
		$this->dbus = new \DBus(\Dbus::BUS_SESSION, true);
		$this->proxy = $this->dbus->createProxy("com.Skype.API", "/com/Skype", "com.Skype.API");
	}

	private function _sendCommand($command)
	{
		$this->log->addDebug($command);

		$response = $this->proxy->Invoke($command);

		$this->log->addDebug($response);

		return $response;
	}

	public function initialize($params = array())
	{
		if (!isset($params['appname']) or !isset($params['protocol'])) {
			throw new \Exception("The initialize method needs to know the 'appname' and 'protocol' version");
		}

		$this->_sendCommand("NAME ".$params['appname']);
		$this->_sendCommand("PROTOCOL ".$params['protocol']);
	}

	public function isContact($name)
	{
		$friends = $this->getContacts();

		if (!count($friends)) {
			$this->log->addDebug("$name has no contacts");
			return false;
		}

		$iscontact = (in_array($name, $friends));

		if (!$iscontact) {
			$this->log->addDebug("$name is not among contacts: ".implode(",", $friends));
		}

		return $iscontact;
	}

	public function getContacts()
	{
		$result = $this->_sendCommand("SEARCH FRIENDS");
		return explode(", ", substr($result, 6));
	}

	public function getMessageProperties(Chat $chatmsg)
	{
		$properties = array();

		if (!$msgid = $chatmsg->getMessageId()) {
			$this->log->addDebug("ChatMsg did not have messageid");
			return array();
		}

		$result = $this->_sendCommand("GET CHATMESSAGE $msgid BODY");

		$template = "CHATMESSAGE $msgid BODY ";
		$properties['body'] = trim(str_replace($template, "", $result));

		$result = $this->_sendCommand("GET CHATMESSAGE $msgid TIMESTAMP");

		$template = "CHATMESSAGE $msgid TIMESTAMP ";
		$properties['timestamp'] = trim(str_replace($template, "", $result));

		$result = $this->_sendCommand("GET CHATMESSAGE $msgid FROM_HANDLE");

		$template = "CHATMESSAGE $msgid FROM_HANDLE ";
		$contactname = trim(str_replace($template, "", $result));

		if (!isset($this->users[$contactname])) {
			$user = new User($contactname, $chatmsg->getSkybot());

			$result = $this->_sendCommand("GET CHATMESSAGE $msgid FROM_DISPNAME");

			$template = "CHATMESSAGE $msgid FROM_DISPNAME ";

			$user->setDisplayName(trim(str_replace($template, "", $result)));

			$this->users[$contactname] = $user;
		}

		$chatmsg->setUser($this->users[$contactname]);

		return $properties;
	}

	public function markSeen(Chat $chatmsg)
	{
		$this->_sendCommand("SET CHATMESSAGE ".$chatmsg->getMessageId()." SEEN");
	}

	public function getMissedChats()
	{
		$result = $this->_sendCommand("SEARCH MISSEDCHATS");

		$chats = explode(", ", substr($result, 6));

		return $chats;
	}

	public function getRecentChats()
	{
		$result = $this->_sendCommand("SEARCH RECENTCHATS");

		$chats = explode(", ", substr($result, 6));

		return $chats;
	}

	public function getRecentMessagesForChat($chatid, Main $skybot)
	{
		$result = $this->_sendCommand("GET CHAT {$chatid} RECENTCHATMESSAGES");

		$recentmessages = explode(", ", str_replace("CHAT {$chatid} RECENTCHATMESSAGES ", "", $result));

		if (!count($recentmessages)) return array();

		$newmessages = array_diff($recentmessages, $this->messages);

		if (!count($newmessages)) return array();

		$this->messages = array_merge($this->messages, $recentmessages);

		$tmp = array();

		foreach ($newmessages as $msgid) {
			$tmp[] = new Chat($msgid, $chatid, $skybot);
		}

		return $tmp;
	}

	public function getUserProperty($contactname, $property)
	{
	    $property = strtoupper($property);
		$result = $this->_sendCommand("GET USER {$contactname} {$property}");	 
		return trim(str_replace("USER {$contactname} {$property}", "", $result));		   
	}

	public function getChatProperty($chatid, $property)
	{
		$result = $this->_sendCommand("GET CHAT $chatid $property");

		return trim(str_replace("CHAT $chatid $property", "", $result));
	}

	public function sendReply(Reply $reply)
	{
		$this->_sendCommand("CHATMESSAGE ".$reply->getChatMsg()->getChatId()." ".$reply->getBody());
	}

	public function createChatWith(User $user)
	{
		$contactname = $user->getContactName();

		$result = $this->_sendCommand("CHAT CREATE ".$contactname);

		$tmp = explode(' ', $result);

		if (isset($tmp[1])) {
			return $tmp[1];
		} else {
			throw new \Exception("Unable to create chat with ".$contactname);
		}
	}

	public function sendDirectMessage(Direct $dm)
	{
		$this->_sendCommand("CHATMESSAGE ".$dm->getChatId()." ".$dm->getBody());
	}

	public function refuseCalls()
	{
		$result = $this->_sendCommand("SEARCH CALLS");
		$calls = explode(", ", substr($result, 6));

		if (!count($calls)) return true;

		foreach ($calls as $callid) {
			if (!$callid) continue;

			if (isset($this->calls[$callid])) continue;
			$this->calls[$callid] = true;

			$result = $this->_sendCommand("GET CALL $callid STATUS");
			$t = explode(' ', $result);

			if (!isset($t[3])) continue;

			$status = $t[3];

			if ($status == 'RINGING') {
				$this->_sendCommand("ALTER CALL $callid END HANGUP");
			}
		}
	}
}
