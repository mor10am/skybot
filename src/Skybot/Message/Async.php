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

class Async
{
	public $messageid;
	public $body;
	public $timestamp;
	public $user;
	public $chatid;
	public $result;
	public $plugin;
	public $dm;
	public $skybot;

	public function reply($txt)
	{
		if (!$this->skybot) {
			return $txt;
		}

		$port = $this->skybot->getConfig()->getServerPort();

		if (!$port) return false;

		if (!$this->user) {
			$this->user = new User('async', $this->skybot);
		}

		$txt = "[".$this->chatid."][".$this->user->getContactName()."] echo ".$txt;

		if (!$socket = fsockopen('127.0.0.1', $port, $errno, $errstr, 2)) {
			$this->skybot->getLog()->addError($errstr);
			return false;
		}

		fwrite($socket, $txt, strlen($txt));
		fflush($socket);
		fclose($socket);

		$socket = null;

		return true;
	}

	public function setDM()
	{
		$this->dm = true;
	}

	public function isDM()
	{
		return $this->dm;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function getChatId()
	{
		return $this->chatid;
	}

	public function getBody()
	{
		return $this->body;
	}
}

