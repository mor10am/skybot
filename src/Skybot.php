<?php

class Skybot
{
	private $skype;
	private $plugins;

	public function __construct($skype, $plugins)
	{
		$this->skype = $skype;
		$this->plugins = $plugins;
	}	

	public function run()
	{
		do {
		    $this->skype->handleChatMessages();

		    $this->skype->waitLoop(1000);
		} while(true);		
	}
}

