<?php

namespace Test\Skybot;

use Skybot\Main;

class MainTest extends \PHPUnit_Framework_TestCase
{
	public function testMultilineTcpMessage()
	{
		$txt = "[string1][string2] echo NetMon Alarm
10.0.40.5 (IVR),  is down!";

		$matches = Main::parseTcpMessage($txt);

		$this->assertEquals(4, count($matches));
		$this->assertEquals('string1', $matches[1]);
		$this->assertEquals('string2', $matches[2]);
		$this->assertEquals('echo NetMon Alarm
10.0.40.5 (IVR),  is down!', $matches[3]);
	}

	public function testMultilineTcpMessageWithMoreSquareBraces()
	{
		$txt = "[string1][string2] echo [NetMon Alarm]
10.0.40.5 (IVR),  is down!";

		$matches = Main::parseTcpMessage($txt);
		$this->assertEquals(4, count($matches));
		$this->assertEquals('string1', $matches[1]);
		$this->assertEquals('string2', $matches[2]);
		$this->assertEquals('echo [NetMon Alarm]
10.0.40.5 (IVR),  is down!', $matches[3]);
	}
}