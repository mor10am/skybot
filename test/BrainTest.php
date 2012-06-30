<?php

class BrainTest extends \PHPUnit_Framework_TestCase
{
	public function testSet()
	{
		$plugin = new \Skybot\Plugin\Brain();

		$dic = new \Pimple();
		$dic['storage'] = new \Skybot\Storage('test.db');

		$message = new \Skybot\Skype\Message(null, null, $dic);

		$message->setBody("@set test vas");
		$message->setSkypeName("myskypename");

		$response = $plugin->run($message);

		$this->assertEquals('Saved key test', $response);
	}

	public function testGet()
	{
		$plugin = new \Skybot\Plugin\Brain();

		$dic = new \Pimple();
		$dic['storage'] = new \Skybot\Storage('test.db');

		$message = new \Skybot\Skype\Message(null, null, $dic);

		$message->setBody("@get test");
		$message->setSkypeName("myskypename");

		$response = $plugin->run($message);

		$this->assertEquals('test=vas', $response);
	}

	public function testGetNoValue()
	{
		$plugin = new \Skybot\Plugin\Brain();

		$dic = new \Pimple();
		$dic['storage'] = new \Skybot\Storage('test.db');

		$message = new \Skybot\Skype\Message(null, null, $dic);

		$message->setBody("@get test2");
		$message->setSkypeName("myskypename");

		$response = $plugin->run($message);

		$this->assertEquals('Key test2 does not have any value', $response);
	}
}

