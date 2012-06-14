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
	protected $async = false;

	public function __construct(\Pimple $dic = null)
	{
		$this->dic = $dic;
	}

	public function run(Message $chatmsg)
	{		
		if (!$this->getRegexp()) continue;
		if (!$matches = preg_match($this->getRegexp(), $chatmsg->getBody(), $result)) continue;

		if (isset($result[1]) and trim($result[1]) == 'me') {
			$chatmsg->setDM();
		}

		if ($this->async) {
			$asyncmsg = $chatmsg->createAsyncMessage();
			$asyncmsg->result = $result;
			$asyncmsg->plugin = get_class($this);

			$payload = base64_encode(serialize($asyncmsg));

			$this->dic['log']->addDebug("Run {$asyncmsg->plugin} ASYNC for {$asyncmsg->skypename}");

			$cmd = "/usr/bin/daemon ".__DIR__ . "/../../async.php $payload";

			$this->dic['log']->addDebug($cmd);
			
			exec($cmd, $retval);

			return true;

		} else {
			return $this->handle($chatmsg, $result);
		}
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