<?php

namespace Skybot;

use Skybot\Skype;
use Skybot\Skype\Message;
use Skybot\Skype\Reply;
use Skybot\Plugin;

class PluginContainer
{
	private $plugins = array();
	private $filters = array();
	private $dic;	

	public function __construct(\Pimple $dic)
	{
		$this->dic = $dic;
	}

	public function parseMessage(Message $chatmsg)
	{
		foreach ($this->getFilters() as $filter) {
			if ($filter->beforePlugins()) {
				$ret = $filter->handle($chatmsg);
				if (!$ret instanceof Message) break;
				$chatmsg = $ret;
			}
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

		foreach ($this->getFilters() as $filter) {
			if ($filter->afterPlugins()) {
				$ret = $filter->handle($chatmsg);
				if (!$ret instanceof Message) break;
				$chatmsg = $ret;
			}
		}
	}

	public function addPlugin(PluginInterface $plugin)
	{
		$id = md5(get_class($plugin).$plugin->getRegExp());

		if (!isset($this->plugins[$id])) {
			$this->plugins[$id] = $plugin;
			return true;
		}
	}

	public function addFilter(FilterInterface $filter)
	{
		$id = $filter->getPri().'_'.get_class($filter);

		if (isset($this->filters[$id])) return true;

		$this->filters[$id] = $filter;

		ksort($this->filters);
	}


	public function getPlugins()
	{
		return $this->plugins;
	}

	public function getFilters()
	{
		return $this->filters;
	}	
}