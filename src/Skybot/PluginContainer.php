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

use Skybot\Message\Chat;
use Skybot\Message\Reply;
use Skybot\Plugin;

class PluginContainer
{
	private $plugins = array();
	private $filters = array();

	private $skybot;

	public function __construct(\Skybot\Main $skybot = null)
	{
		$this->skybot = $skybot;
	}

	public function getSkybot()
	{
		return $this->skybot;
	}

	public function parseMessage(Chat $chatmsg)
	{
		foreach ($this->getFilters() as $filter) {
			if ($filter->beforePlugins()) {
				$ret = $filter->handle($chatmsg);
				if (!$ret instanceof Chat) break;
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
				if (!$ret instanceof Chat) break;
				$chatmsg = $ret;
			}
		}

		return $chatmsg;
	}

	public function addPlugin(PluginInterface $plugin)
	{
		if (!$plugin->getRegexp()) throw new \Exception("Plugin has not regular expression.");

		$id = get_class($plugin);

		if (!isset($this->plugins[$id])) {
			$this->plugins[$id] = $plugin;

			if ($this->skybot) {
				$this->skybot->getLog()->addDebug("Added plugin ".get_class($plugin));
			}

			return true;
		}
	}

	public function addFilter(FilterInterface $filter)
	{
		$id = $filter->getPri().'_'.get_class($filter);

		if (isset($this->filters[$id])) return true;


		$this->filters[$id] = $filter;

		if ($this->skybot) {
			$this->skybot->getLog()->addDebug("Added filter ".get_class($filter));
		}

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
					$plugin = new $classname($this->skybot);

					if ($plugin instanceof \Skybot\BasePlugin) {
						$this->addPlugin($plugin);
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
						$filter = new $classname($this->skybot);

						if ($filter instanceof \Skybot\BaseFilter) {
							$this->addFilter($filter);
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