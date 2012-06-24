<?php

/*
* This file is part of Skybot
*
* (c) 2012 Morten Amundsen <mor10am@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

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

