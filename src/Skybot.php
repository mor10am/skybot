<?php

class Skybot
{
	private $dic;

	public function __construct(\Pimple $dic)
	{
		$this->dic = $dic;		
	}	

	public function run()
	{
		do {
		    $this->dic['skype']->handleChatMessages();

		    $this->dic['skype']->waitLoop(500);

		} while(true);		
	}
}

