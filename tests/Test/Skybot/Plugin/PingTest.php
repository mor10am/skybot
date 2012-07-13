<?php

namespace Test\Skybot\Plugin;

use Skybot\Driver\Dummy;
use Skybot\Config;
use Monolog\Logger;
use Monolog\Handler\NullHandler;

class PingTest extends \PHPUnit_Framework_TestCase
{
	private $skybot;

	protected function setUp()
	{
		$log = new Logger('test');
		$log->pushHandler(new NullHandler());

		$this->skybot = new \Skybot\Main(new Dummy(), new Config(), $log);
	}

	public function testPing()
	{
		$plugin = new \Skybot\Plugin\Ping($this->skybot);

		$message = new \Skybot\Message\Chat();

		$message->setBody("ping");
		$message->setContactName("myskypename");

		$response = $plugin->run($message);

		$this->assertEquals($response, "Hi, myskypename. Pong!");
		$this->assertFalse($message->isDM());
	}

	public function testPingDM()
	{
		$plugin = new \Skybot\Plugin\Ping($this->skybot);

		$message = new \Skybot\Message\Chat();

		$message->setBody("ping me");
		$message->setContactName("myskypename");

		$response = $plugin->run($message);

		$this->assertEquals($response, "Hi, myskypename. Pong!");
		$this->assertTrue($message->isDM());
	}

	public function testFailedPing()
	{
		$plugin = new \Skybot\Plugin\Ping($this->skybot);

		$message = new \Skybot\Message\Chat();

		$message->setBody("pinga");
		$message->setContactName("myskypename");

		$response = $plugin->run($message);

		$this->assertFalse($response);
	}

}

