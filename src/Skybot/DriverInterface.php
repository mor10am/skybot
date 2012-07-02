<?php

namespace Skybot;

interface DriverInterface
{
	function __construct();
	function initialize($params = array());
	function waitLoop($millisec);
	function sendCommand($command);
	function isContact($name);
}