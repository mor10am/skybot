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
		if (!$this->getRegexp()) return false;
		if (!$matches = preg_match($this->getRegexp(), $chatmsg->getBody(), $result)) return false;

		if (isset($result[1]) and trim($result[1]) == 'me') {
			$chatmsg->setDM();
		}

		if ($this->async) {
			$asyncmsg = $chatmsg->createAsyncMessage();
			$asyncmsg->result = $result;
			$asyncmsg->plugin = get_class($this);

			$payload = base64_encode(serialize($asyncmsg));

			$this->dic['log']->addDebug("Run {$asyncmsg->plugin} ASYNC for {$asyncmsg->skypename}");

			$dir = false;

			if (isset($this->dic['config'])) {
				$dir = $this->dic['config']->bin_dir;
				if ($dir) {
					$dir = $dir."/";
				}
			}

			$cmd = "/usr/bin/daemon ".$dir."async.php $payload";

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