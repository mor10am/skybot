<?php

namespace Skybot;

use Skybot\Skype;
use Skybot\Skype\Message;
use Skybot\Skype\Reply;
use Skybot\Plugin;

class PluginContainer
{
	private $plugins = array();
	private $dic;	

	public function __construct(\Pimple $dic)
	{
		$this->dic = $dic;
	}

	public function parseMessage(Message $chatmsg)
	{
		if ($this->dic['log']) {
			$this->dic['log']->addInfo($chatmsg->getSkypeName()." to Skybot : ".$chatmsg->getBody());
		}

		foreach ($this->getPlugins() as $plugin) {
			try {
				$response = $plugin->run($chatmsg);

				if ($response === false) continue;

				if ($response and is_string($response)) {
					$chatmsg->reply($response);
				}

				break;

			} catch (\Exception $e) {
				$chatmsg->reply(new Reply($chatmsg, $e->getMessage(), $dm));
			}
		}		
	}

	public function add(PluginInterface $plugin)
	{
		$id = md5(get_class($plugin).$plugin->getRegExp());

		if (!isset($this->plugins[$id])) {
			$this->plugins[$id] = $plugin;
			return true;
		}
	}

	public function getPlugins()
	{
		return $this->plugins;
	}
}