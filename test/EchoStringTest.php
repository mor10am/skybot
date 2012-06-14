<?php

class EchoStringTest extends \PHPUnit_Framework_TestCase
{
	public function testEcho()
	{
		$plugin = new \Skybot\Plugin\EchoString();

		$message = new \Skybot\Skype\Message();

		$message->setBody("echo test");
		$message->setSkypeName("myskypename");

		$response = $plugin->run($message);

		$this->assertEquals($response, "test");
	}
}

