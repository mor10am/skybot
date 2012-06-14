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
		foreach ($this->getPlugins() as $plugin) {
			try {
				if (!$plugin->getRegexp()) continue;
				if (!$matches = preg_match($plugin->getRegexp(), $chatmsg->getBody(), $result)) continue;

				if (isset($result[1]) and trim($result[1]) == 'me') {
					$chatmsg->setDM();
				}

				if ($this->dic['log']) {
					$this->dic['log']->addInfo($chatmsg->getSkypeName()." to Skybot : ".$chatmsg->getBody());
				}

				$response = $plugin->run($chatmsg, $result);

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