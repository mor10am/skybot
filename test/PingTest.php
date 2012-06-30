<?php

class PingTest extends \PHPUnit_Framework_TestCase
{
	public function testPing()
	{
		$plugin = new \Skybot\Plugin\Ping();

		$message = new \Skybot\Skype\Message();

		$message->setBody("ping");
		$message->setSkypeName("myskypename");

		$response = $plugin->run($message);

		$this->assertEquals($response, "Hi, myskypename. Pong!");
		$this->assertFalse($message->isDM());
	}

	public function testPingDM()
	{
		$plugin = new \Skybot\Plugin\Ping();

		$message = new \Skybot\Skype\Message();

		$message->setBody("ping me");
		$message->setSkypeName("myskypename");

		$response = $plugin->run($message);

		$this->assertEquals($response, "Hi, myskypename. Pong!");
		$this->assertTrue($message->isDM());
	}

	public function testFailedPing()
	{
		$plugin = new \Skybot\Plugin\Ping();

		$message = new \Skybot\Skype\Message();

		$message->setBody("pinga");
		$message->setSkypeName("myskypename");

		$response = $plugin->run($message);

		$this->assertFalse($response);
	}

}

