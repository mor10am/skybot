<?php

namespace Test\Skybot\Plugin;

use Skybot\Driver\Dummy;
use Skybot\Config;
use Monolog\Logger;
use Monolog\Handler\NullHandler;

class AskTest extends \PHPUnit_Framework_TestCase
{
	private $skybot;

	protected function setUp()
	{
		$log = new Logger('test');
		$log->pushHandler(new NullHandler());

		$this->skybot = new \Skybot\Main(new Dummy(), new Config(), $log);
		$this->skybot->setPluginContainer(new \Skybot\PluginContainer($this->skybot));
	}

	public function testAsk()
	{
		$pc = $this->skybot->getPluginContainer();

		$plugin = new \Skybot\Plugin\Ask($this->skybot);

		$pc->addPlugin($plugin);

		$message = new \Skybot\Message\Chat();

		$message->setBody("ask what is your name");
		$message->setContactName("myskypename");

		$response = $pc->parseMessage($message);

		$this->assertEquals($response, "what is your name?");

		$message = new \Skybot\Message\Chat();

		$message->setBody("Morten");
		$message->setContactName("myskypename");

		$response = $pc->parseMessage($message);

		$this->assertEquals($response, "You answered: Morten");
	}
}

