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
		if (preg_match("/^echo( me)? (.*)$/", $chatmsg->getBody(), $result)) {
			$dm = false;

			if (isset($result[1]) and trim($result[1]) == 'me') {
				$dm = true;
			}

			return $chatmsg->reply(new Reply($chatmsg, $result[2], $dm));
		}


		if (preg_match("/^plugins$/", $chatmsg->getBody())) {
			return $this->builtinListPlugins($chatmsg);						
		}

		foreach ($this->getPlugins() as $plugin) {
			try {
				$reply = $plugin->parse($chatmsg);
				
				if ($reply instanceof Reply) {
					$chatmsg->reply($reply);
					break;
				}
			} catch (\Exception $e) {
				$chatmsg->reply(new Reply($chatmsg, $e->getMessage()));
			}
		}		
	}

	public function builtinListPlugins(Message $chatmsg)
	{
		$dic = $chatmsg->getDic();
		$plugins = $dic['plugincontainer']->getPlugins();

		$txt = "\r\nAvailable plugins:\r\n\r\n";

		$i = 1;

		$txt .= $i.") List all plugins (/^plugins$/)\r\n";

		$i++;

		$txt .= $i.") Echo back the same text (/^echo( me)? (.*)$/)\r\n";

		$i++;

		foreach ($plugins as $plugin) {
			$txt .= $i.") ".$plugin->getDescription()." (".$plugin->getRegexp().")\r\n";
			$i++;
		}

		$chatmsg->reply(new Reply($chatmsg, $txt, true));
	}

	public function add(PluginInterface $plugin)
	{
		$this->plugins[] = $plugin;
	}

	public function getPlugins()
	{
		return $this->plugins;
	}
}