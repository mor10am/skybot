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
use Symfony\Component\Finder\Finder;

class Main extends EventDispatcher
{
	private $timestamp;

	private $user;

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

		$this->user = new User($config->getContactName(), $this);
		$this->timestamp = time();

		$port = $config->getServerPort();

		if ($port and is_numeric($port)) {
			$this->socket = socket_create_listen($port);
			socket_set_nonblock($this->socket);
		} else {
			$port = "<NO PORT>";
		}

		$log->addInfo("Starting Skybot as ".$this->user->getContactName()." and listening on port $port");
	}

	public function getUser()
	{
		return $this->user;
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

	public function handle()
	{
		$this->_refuseCalls();

		$this->_handleMessagesFromPort();

		$this->_handleChatMessages();
	}

	private function _handleChatMessages()
	{
		$chats = $this->_getMissedChats();

		if (!count($chats)) return false;

		foreach ($chats as $chatid) {
			if (!$chatid) continue;
			$this->_loadAndEmitChatMessages($chatid);
		}
	}

	private function _loadAndEmitChatMessages($chatid)
	{
		$recentmessages = $this->getDriver()->getRecentMessagesForChat($chatid, $this);

		if (!count($recentmessages)) return true;

		foreach ($recentmessages as $chatmsg) {
			if ($chatmsg->getUser()->getContactName() == $this->getUser()->getContactName() or $chatmsg->getTimestamp() < $this->timestamp or $chatmsg->isEmpty()) {
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

	private function _handleMessagesFromPort()
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
			$body = $matches[3]." [".$contactname."]";

			if (!$chatid = $this->findChat($chatname)) {
				$this->log->addWarning("No chat ($chatname) found for message: ".$txt);
				continue;
			}

			$chatmsg = new Chat(null, $chatid, $this);
			$chatmsg->setBody($body);
			$chatmsg->setUser(new User($contactname, $this));
			$chatmsg->setInternal();

			$this->dispatch('skybot.message', $chatmsg);
		}
	}

	public static function parseTcpMessage($txt)
	{
		if (!preg_match("/\[(.{1,})?\]\[(\w{1,})?\]\s(.*)$/ms", $txt, $matches)) return false;

		return $matches;
	}

	public function findChat($chatname)
	{
		$chatid = false;

		if (substr($chatname, 0, 1) == '#') {
			$this->chatnames[$chatname] = $chatname;
		} else {
			$chatname = mb_strtolower($chatname);
		}

		if (isset($this->chatnames[$chatname])) {
			$chatid = $this->chatnames[$chatname];
		} else {
			$chats = $this->_getRecentChats();

			if (!count($chats)) return false;

			$len = mb_strlen($chatname);

			foreach ($chats as $cid) {
				$friendlyname = mb_strtolower($this->getDriver()->getChatProperty($cid, 'FRIENDLYNAME'));

				$this->chatnames[$friendlyname] = $cid;

				if (mb_substr($friendlyname, 0, $len) == $chatname) {
					$this->chatnames[$chatname] = $cid;
					$chatid = $cid;
					break;
				}
			}
		}

		return $chatid;
	}

	public function reply(Reply $reply)
	{
		$this->messages_served++;

		if ($reply->isDM()) {
			$this->directMessage($reply->createDirectMessage());
		} else {
			$this->getDriver()->sendReply($reply);

			$this->log->addInfo("Skybot reply to ".$reply->getChatMsg()->getUser()->getDisplayName(). " : ".$reply->getBody());
		}
	}

	public function directMessage(Direct $dm)
	{
		if (!isset($this->personalchats[$dm->getUser->getContactName()])) {

			$chatid = $this->getDriver()->createChatWith($dm->getUser());

			$this->personalchats[$dm->getContactName()] = $chatid;

		} else {
			$chatid = $this->personalchats[$dm->getUser()->getContactName()];
		}

		$this->getDriver()->sendDirectMessage($dm);

		$this->log->addInfo("Skybot DM to ".$dm->getUser()->getContactName(). " : ".$dm->getBody());
	}

	private function _refuseCalls()
	{
		$this->getDriver()->refuseCalls();
	}
}
