<?php

namespace Test\Skybot\Plugin;

use Skybot\Driver\Dummy;
use Skybot\Config;
use Monolog\Logger;
use Monolog\Handler\NullHandler;

class EchoStringTest extends \PHPUnit_Framework_TestCase
{
	private $skybot;

	protected function setUp()
	{
		$log = new Logger('test');
		$log->pushHandler(new NullHandler());

		$this->skybot = new \Skybot\Main(new Dummy(), new Config(), $log);
	}

	public function testEcho()
	{
		$plugin = new \Skybot\Plugin\EchoString($this->skybot);

		$message = new \Skybot\Message\Chat();

		$message->setBody("echo test");
		$message->setContactName("myskypename");

		$response = $plugin->run($message);

		$this->assertEquals($response, "test");
	}
}

