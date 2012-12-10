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

use Skybot\Main;

class User
{
	private $contactname;
	private $displayname;
	private $skybot;

	public function __construct($contactname, Main $skybot = null)
	{
		$this->contactname = $contactname;
		$this->skybot = $skybot;
	}

	public function getContactName()
	{
		return $this->contactname;
	}

	public function setDisplayName($displayname)
	{
		$this->displayname = $displayname;
	}

	public function getDisplayName()
	{
		if (!$this->displayname) return $this->contactname;
		return $this->displayname;
	}

	public function getOnlineStatus()
	{
	    return $this->skybot->getDriver()->getUserProperty($this->contactname, 'ONLINESTATUS');
	}
}
