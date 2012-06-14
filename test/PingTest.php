<?php

class PingTest extends \PHPUnit_Framework_TestCase
{
	public function testPing()
	{
		$plugin = new \Skybot\Plugin\Ping();

		$message = new \Skybot\Skype\Message();

		$message->setBody("ping me");
		$message->setSkypeName("myskypename");

		$response = $plugin->run($message);

		$this->assertEquals($response, "Yo! Pong to you myskypename!");
	}
}

