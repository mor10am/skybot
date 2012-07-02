<?php

use Skybot\Driver\Dummy;
use Skybot\Config;
use Monolog\Logger;
use Monolog\Handler\NullHandler;

class BrainTest extends \PHPUnit_Framework_TestCase
{
	private $skybot;

	protected function setUp()
	{
		$log = new Logger('test');
		$log->pushHandler(new NullHandler());

		$this->skybot = new \Skybot\Main(new Dummy(), new Config(), $log);
	}

	public function testSet()
	{
		$plugin = new \Skybot\Plugin\Brain($this->skybot);

		$message = new \Skybot\Message\Chat(null, null, $this->skybot);

		$message->setBody("@set test vas");
		$message->setSkypeName("myskypename");

		$response = $plugin->run($message);

		$this->assertEquals('Saved key test', $response);
	}

	public function testGet()
	{
		$plugin = new \Skybot\Plugin\Brain($this->skybot);

		$message = new \Skybot\Message\Chat(null, null, $this->skybot);

		$message->setBody("@get test");
		$message->setSkypeName("myskypename");

		$response = $plugin->run($message);

		$this->assertEquals('test=vas', $response);
	}

	public function testGetNoValue()
	{
		$plugin = new \Skybot\Plugin\Brain($this->skybot);

		$message = new \Skybot\Message\Chat(null, null, $this->skybot);

		$message->setBody("@get test2");
		$message->setSkypeName("myskypename");

		$response = $plugin->run($message);

		$this->assertEquals('Key test2 does not have any value', $response);
	}
}

