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
	private $cronjobs = array();

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

	public function handle()
	{
		$this->_refuseCalls();

		$this->_handleCronJobs();

		$this->_handleMessagesFromPort();

		$this->_handleChatMessages();
	}

	private function _handleCronJobs()
	{
		foreach ($this->cronjobs as $job) {
			try {
				if ($chatmsg = $job->run()) {
					if ($chatmsg instanceof Chat and $chatmsg->getChatId()) {
						$this->dispatch('skybot.message', $chatmsg);
					}
				}
			} catch (\Exception $e) {
				$this->log->addError($e->getMessage());
			}
		}
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

			if (!$chatid = $this->findChat($chatname)) continue;

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

			foreach ($chats as $cid) {
				$friendlyname = mb_strtolower($this->getDriver()->getChatProperty($cid, 'FRIENDLYNAME'));

				$this->chatnames[$friendlyname] = $cid;

				if (strpos($friendlyname, $chatname) !== false) {
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

	public function getCronjobs()
	{
		return $this->cronjobs;
	}

	public function loadCronJobs($filterdirs)
	{
		$finder = new Finder();

		if (!is_array($filterdirs)) {
			$filterdirs = array($filterdirs);
		}

		foreach ($filterdirs as $dir) {
			if (!$dir) continue;

			try {
				$finder->files()->in($dir)->name("*.php");

				foreach ($finder as $file) {
					require_once $file;

					$classname = "\\Skybot\\Cron\\".basename($file->getFileName(), ".php");

					$implements = class_implements($classname);

					if (!$implements) continue;

					if (in_array("Skybot\\CronInterface", $implements)) {
						if (isset($this->cronjobs[$classname])) continue;

						$cronjob = new $classname($this);

						if ($cronjob instanceof \Skybot\BaseCron) {
							$this->cronjobs[$classname] = $cronjob;
							$this->getLog()->addDebug("Added cron ".get_class($cronjob));
						} else {
							throw new \Exception("$classname is not instance of Skybot\\BaseCron\n");
						}
					} else {
						throw new \Exception("$classname is does not implement Skybot\\CronInterface\n");
					}
				}
			} catch (\InvalidArgumentException $e) {

			}
		}
	}
}
