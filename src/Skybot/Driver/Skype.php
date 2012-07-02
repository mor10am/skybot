<?php

namespace Skybot\Driver;

use Skybot\DriverInterface;

class Skype implements DriverInterface
{
	private $dbus;
	private $proxy;

	public function __construct()
	{
        $this->dbus = new \DBus(\Dbus::BUS_SESSION, true);
        $this->proxy = $this->dbus->createProxy("com.Skype.API", "/com/Skype", "com.Skype.API");
        $this->initialize();
	}

	public function initialize($params = array())
	{
        $this->sendCommand("NAME SKYBOT");
        $this->sendCommand("PROTOCOL 7");
	}

	public function waitLoop($millisec)
	{
		$this->dbus->waitLoop($millisec);
	}

	public function sendCommand($command)
	{
    	return $this->proxy->Invoke($command);
	}

	public function isContact($name)
	{
        $result = $this->sendCommand("SEARCH FRIENDS");
        $friends = explode(", ", substr($result, 6));

        if (!count($friends)) return false;

        return (in_array($name, $friends));
	}
}