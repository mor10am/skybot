<?php

namespace Skybot\Driver;

use Skybot\DriverInterface;

class Dummy implements DriverInterface
{
	public function __construct()
	{
	}

	public function initialize($params = array())
	{
	}

	public function waitLoop($millisec)
	{
		usleep($millisec*1000);
	}

	public function sendCommand($command)
	{
		return true;
	}

	public function isContact($name)
	{
		return true;
	}
}