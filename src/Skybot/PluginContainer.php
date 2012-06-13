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
		if ($this->_builtinEcho($chatmsg)) return true;
		if ($this->_builtinListPlugins($chatmsg)) return true;
		if ($this->_builtinHealth($chatmsg)) return true;

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

				$plugin->run($chatmsg, $result);

				break;

			} catch (\Exception $e) {
				$chatmsg->reply(new Reply($chatmsg, $e->getMessage(), $dm));
			}
		}		
	}

	private function _builtinHealth(Message $chatmsg)
	{
		if (!preg_match("/^health( me)?$/", $chatmsg->getBody(), $result)) return false;

		$dm = false;

		if (isset($result[1]) and trim($result[1]) == 'me') {
			$dm = true;
		}

		$mem = round(memory_get_usage(true) / (1024*1024), 2);
		$peak = round(memory_get_peak_usage(true) / (1024*1024), 2);

		$txt = "Skybot healthcheck:\n\n";
		$txt .= "Uptime: ".round((time()-$this->dic['skype']->timestamp) / 60) . " minutes\n";
		$txt .= "Memory usage: ".$mem." Mb\n";
		$txt .= "Memory peak usage: ".$peak." Mb\n";
		$txt .= "Messages served: ".$this->dic['skype']->messages_served."\n";

		return $chatmsg->reply(new Reply($chatmsg, $txt, $dm));
	}

	private function _builtinEcho(Message $chatmsg)
	{
		if (!preg_match("/^echo( me)? (.*)$/", $chatmsg->getBody(), $result)) return false;

		$dm = false;

		if (isset($result[1]) and trim($result[1]) == 'me') {
			$dm = true;
		}

		return $chatmsg->reply(new Reply($chatmsg, $result[2], $dm));
	}

	private function _builtinListPlugins(Message $chatmsg)
	{
		if (!preg_match("/^plugins$/", $chatmsg->getBody())) return false;

		$dic = $chatmsg->getDic();
		$plugins = $dic['plugincontainer']->getPlugins();

		$txt = "\r\nAvailable plugins:\r\n\r\n";

		$i = 1;

		$txt .= $i.") List all plugins (/^plugins$/)\r\n";

		$i++;

		$txt .= $i.") Echo back the same text (/^echo( me)? (.*)$/)\r\n";

		$i++;

		$txt .= $i.") Get health statistics of Skybot (/^health( me)?$/)\r\n";

		$i++;		

		foreach ($plugins as $plugin) {
			$txt .= $i.") ".$plugin->getDescription()." (".$plugin->getRegexp().")\r\n";
			$i++;
		}

		return $chatmsg->reply(new Reply($chatmsg, $txt, true));
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