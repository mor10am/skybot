<?php

require_once 'PluginInterface.php';
require_once 'BasePlugin.php';
require_once 'Plugin/Ping.php';
require_once 'Plugin/CDK.php';

class PluginContainer
{
	public $plugins = array();

	public function __construct($skype = null)
	{
		$this->plugins[] = new Plugin_Ping($skype);
		$this->plugins[] = new Plugin_CDK($skype);
	}

	public function handle($messages)
	{
		if (!count($messages)) return false;

		foreach ($messages as $msg) {
			if ($msg->isMarked()) continue;

			foreach ($this->plugins as $plugin) {
				try {
					if ($plugin->parse($msg)) break;				
				} catch (Exception $e) {
					$msg->reply($e->getMessage());
				}
			}

			$msg->mark();
		}
	}
}