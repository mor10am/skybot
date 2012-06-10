<?php

namespace Skybot\Skype;

class DirectMessage
{
	private $skypename;
	private $body;

	public function __construct($skypename, $body)
	{
		$this->skypename = $skypename;
		$this->body = $body;
	}

	public function getSkypeName()
	{
		return $this->skypename;
	}

	public function getBody()
	{
		return $this->body;
	}
}