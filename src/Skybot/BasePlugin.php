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

		if (!$chatmsg->isInternal()) {
			if (isset($this->dic['skype'])) {
				if (!$this->dic['skype']->isFriend($chatmsg->getSkypeName())) {
					if ($this->dic['log']) {
						$this->dic['log']->addWarning($chatmsg->getSkypeName()." is not a friend.");
					}
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

			$this->dic['log']->addDebug("Run {$asyncmsg->plugin} ASYNC for {$asyncmsg->skypename}");

            $dir = false;

            if (isset($this->dic['config'])) {
                $dir = $this->dic['config']->base_dir;

                if ($dir) {
                        $dir = $dir."/";
                }

                $cmd = $this->dic['config']->async_cmd;
            }

            $cmd = "/usr/bin/daemon --chdir=".$dir." ".$cmd." ".$payload;

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