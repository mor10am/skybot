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

use Symfony\Component\Finder\Finder;
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

		return $chatmsg;
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

	public function loadPlugins($plugindirs)
	{
		$finder = new Finder();

		if (!is_array($plugindirs)) {
			$plugindirs = array($plugindirs);
		}

		foreach ($plugindirs as $dir) {
			if (!$dir) continue;

			$finder->files()->in($dir)->name("*.php");

			foreach ($finder as $file) {
				require_once $file;

				$classname = "\\Skybot\\Plugin\\".basename($file->getFileName(), ".php");

				$implements = class_implements($classname);

				if (!$implements) continue;

				if (in_array("Skybot\\PluginInterface", $implements)) {
					$plugin = new $classname($this->dic);

					if ($plugin instanceof \Skybot\BasePlugin) {
						if ($this->addPlugin($plugin)) {
							$this->dic['log']->addDebug("Added plugin $classname : ".$plugin->getDescription());
						}
					} else {
						throw new \Exception("$classname is not instance of Skybot\\BasePlugin\n");
					}
				} else {
					throw new \Exception("$classname is does not implement Skybot\\PluginInterface\n");
				}
			}
		}
	}

	public function loadFilters($filterdirs)
	{
		$finder = new Finder();

		if (!is_array($filterdirs)) {
			$filterdirs = array($filterdirs);
		}

		foreach ($filterdirs as $dir) {
			if (!$dir) continue;

			try {
				$finder->files()->in($dir)->name("*.php");

				foreach ($finder as $file) {
					require_once $file;

					$classname = "\\Skybot\\Filter\\".basename($file->getFileName(), ".php");

					$implements = class_implements($classname);

					if (!$implements) continue;

					if (in_array("Skybot\\FilterInterface", $implements)) {
						$filter = new $classname($this->dic);

						if ($filter instanceof \Skybot\BaseFilter) {
							if ($this->addFilter($filter)) {
								$this->dic['log']->addDebug("Added filter $classname : ".$filter->getDescription());
							}
						} else {
							throw new \Exception("$classname is not instance of Skybot\\BaseFilter\n");
						}
					} else {
						throw new \Exception("$classname is does not implement Skybot\\FilterInterface\n");
					}
				}
			} catch (InvalidArgumentException $e) {

			}
		}
	}
}