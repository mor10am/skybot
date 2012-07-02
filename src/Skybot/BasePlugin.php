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

use Skybot\Message\Chat;
use Skybot\Message\Reply;

abstract class BasePlugin
{
	protected $skybot;
	protected $regexp;
	protected $description;
	protected $async = false;

	public function __construct(\Skybot\Main $skybot)
	{
		$this->skybot = $skybot;
		$this->initialize();
	}

	public function initialize()
	{
	}

	public function run(Chat $chatmsg)
	{
		if (!$this->getRegexp()) return false;
		if (!$matches = preg_match($this->getRegexp(), $chatmsg->getBody(), $result)) return false;

		if (!$chatmsg->isInternal()) {
			if (isset($this->skybot)) {
				if (!$this->skybot->isFriend($chatmsg->getSkypeName())) {
					$this->skybot->getLog()->addWarning($chatmsg->getSkypeName()." is not a friend.");

					return true;
				}
			}
		}

		if (isset($result[1]) and trim($result[1]) == 'me') {
			$chatmsg->setDM();
		}

		if ($this->async) {
			$asyncmsg = $chatmsg->createAsyncMessage();
			$asyncmsg->result = $result;
			$asyncmsg->plugin = get_class($this);

			$payload = base64_encode(serialize($asyncmsg));

			$this->skybot->getLog()->addDebug("Run {$asyncmsg->plugin} ASYNC for {$asyncmsg->skypename}");

            $dir = false;

            $config = $this->skybot->getConfig();
            $dir = $config->base_dir;

            if ($dir) {
                    $dir = $dir."/";
            }

            $cmd = $this->dic['config']->async_cmd;

            $cmd = "/usr/bin/daemon --chdir=".$dir." ".$cmd." ".$payload;

			$this->skybot->getLog()->addDebug($cmd);

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