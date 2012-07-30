<?php

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
}