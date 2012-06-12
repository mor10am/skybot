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
	protected $async = false;

	public function __construct(\Pimple $dic = null)
	{
		$this->dic = $dic;
	}

	public function parse(Message $chatmsg)
	{		
		if (!$this->regexp) return false;
		if (!$matches = preg_match($this->regexp, $chatmsg->getBody(), $result)) return false;
		
		$this->result = $result;

		$dm = false;

		if (isset($result[1]) and trim($result[1]) == 'me') {
			$dm = true;
		}

		if ($this->dic['log']) {
			$this->dic['log']->addInfo($chatmsg->getSkypeName()." to Skybot : ".$chatmsg->getBody());
		}

		if ($this->async) {
			$asyncmsg = $chatmsg->createAsyncMessage();
			$asyncmsg->result = $result;
			$asyncmsg->plugin = get_class($this);

			$payload = base64_encode(serialize($asyncmsg));

			$this->dic['log']->addDebug($payload);

			$cmd = "/usr/bin/daemon ".__DIR__ . "/../../async.php $payload";

			$this->dic['log']->addDebug($cmd);
			
			exec($cmd, $retval);

			return true;

		} else {
			return $this->handle($result, $chatmsg, $dm);
		}
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