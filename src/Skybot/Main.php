<?php

/*
* This file is part of Skybot
*
* (c) 2012 Morten Amundsen <mor10am@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Skybot;

use Skybot\DriverInterface;
use Skybot\Config;
use Skybot\Storage;
use Skybot\PluginContainer;

use Skybot\Message\Chat;
use Skybot\Message\Reply;
use Skybot\Message\Direct;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Main extends EventDispatcher
{
	private $timestamp;

	private $contactname;

	private $personalchats = array();
	private $chatnames = array();

	private $clients = array();
	private $socket;

	private $messages_served = 0;

	private $driver;
	private $config;
	private $log;
	private $plugincontainer;

	public function __construct(DriverInterface $driver, Config $config, \Monolog\Logger $log)
	{
		$this->driver = $driver;
		$this->config = $config;
		$this->log = $log;

		$this->contactname = $config->getContactName();
		$this->timestamp = time();

		$port = $config->getServerPort();

		if ($port and is_numeric($port)) {
			$this->socket = socket_create_listen($port);
			socket_set_nonblock($this->socket);
		} else {
			$port = "<NO PORT>";
		}

		$log->addInfo("Starting Skybot as ".$this->contactname." and listening on port $port");
	}

	public function getContactName()
	{
		return $this->contactname;
	}

	public function getStartupTime()
	{
		return $this->timestamp;
	}

	public function getMessagesServed()
	{
		return $this->messages_served;
	}

	public function setPluginContainer(PluginContainer $plugincontainer)
	{
		$this->plugincontainer = $plugincontainer;
	}

	public function getPluginContainer()
	{
		return $this->plugincontainer;
	}

	public function getLog()
	{
		return $this->log;
	}

	public function getConfig()
	{
		return $this->config;
	}

	public function getDriver()
	{
		return $this->driver;
	}

	public function handleChatMessages()
	{
		$this->_refuseCalls();

		$this->_getMessagesFromPort();

		$chats = $this->_getMissedChats();

		if (!count($chats)) return false;

		foreach ($chats as $chatid) {
			if (!$chatid) continue;
			$this->loadAndEmitChatMessages($chatid);
		}
	}

	private function loadAndEmitChatMessages($chatid)
	{
		$recentmessages = $this->getDriver()->getRecentMessagesForChat($chatid);

		if (!count($recentmessages)) return true;

		foreach ($recentmessages as $msgid) {
			$chatmsg = new Chat($msgid, $chatid, $this);

			if ($chatmsg->getContactName() == $this->getContactName() or $chatmsg->getTimestamp() < $this->timestamp or $chatmsg->isEmpty()) {
				continue;
			}

			$this->dispatch('skybot.message', $chatmsg);

			$chatmsg->mark();
		}
	}

	private function _getMissedChats()
	{
		return $this->getDriver()->getMissedChats();
	}

	private function _getRecentChats()
	{
		return $this->getDriver()->getRecentChats();
	}

	public function isContact($contactname)
	{
		return $this->driver->isContact($contactname);
	}

	private function _getMessagesFromPort()
	{
		if (!is_resource($this->socket)) return true;

		if (($client = @socket_accept($this->socket)) !== false) {
			$this->clients[] = $client;
		}

		if (!count($this->clients)) return true;

		$r = $this->clients;
		$w = NULL;
		$e = $this->clients;

		socket_select($r, $w, $e, 0, 500);

		if (count($e)) {
			$this->clients = array_diff($this->clients, $e);
		}

		foreach ($r as $client) {
			$bytes = socket_recv($client, $txt, 1024, MSG_DONTWAIT);
			if (!$bytes) continue;

			socket_getpeername($client, $address, $port);

			$this->log->addWarning("Message from $address : $txt");

			if (!$matches = self::parseTcpMessage($txt)) continue;

			$chatname = mb_strtolower(trim($matches[1]));
			$contactname = trim($matches[2]);
			$body = $matches[3];

			if (substr($chatname, 0, 1) == '#') {
				$this->chatnames[$chatname] = $chatname;
			}

			if (isset($this->chatnames[$chatname])) {
				$chatid = $this->chatnames[$chatname];
			} else {
				$chats = $this->_getRecentChats();

				if (!count($chats)) continue;

				$chatid = false;

				foreach ($chats as $cid) {
					$friendlyname = mb_strtolower($this->getDriver()->getChatProperty($cid, 'FRIENDLYNAME'));

					$this->chatnames[$friendlyname] = $cid;

					if (strpos($friendlyname, $chatname) !== false) {
						$this->chatnames[$chatname] = $cid;
						$chatid = $cid;
						break;
					}
				}

				if (!$chatid) continue;
			}

			$chatmsg = new Chat(null, $chatid, $this);
			$chatmsg->setBody($body);
			$chatmsg->setContactName($contactname);
			$chatmsg->setInternal();

			$this->dispatch('skybot.message', $chatmsg);
		}
	}

	public static function parseTcpMessage($txt)
	{
		if (!preg_match("/\[(.{1,})?\]\[(\w{1,})?\]\s(.*)$/ms", $txt, $matches)) return false;

		return $matches;
	}

	public function reply(Reply $reply)
	{
		$this->messages_served++;

		if ($reply->isDM()) {
			$this->directMessage($reply->createDirectMessage());
		} else {
			$this->getDriver()->sendReply($reply);

			$this->log->addInfo("Skybot reply to ".$reply->getChatMsg()->getDispName(). " : ".$reply->getBody());
		}
	}

	public function directMessage(Direct $dm)
	{
		if (!isset($this->personalchats[$dm->getContactName()])) {

			$chatid = $this->getDriver()->createChatWith($dm->getContactName());

			$this->personalchats[$dm->getContactName()] = $chatid;

		} else {
			$chatid = $this->personalchats[$dm->getContactName()];
		}

		$this->getDriver()->sendDirectMessage($dm);

		$this->log->addInfo("Skybot DM to ".$dm->getContactName(). " : ".$dm->getBody());
	}

	private function _refuseCalls()
	{
		$this->getDriver()->refuseCalls();
	}
}
