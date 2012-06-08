<?php

abstract class BasePlugin
{
	protected $skype;

	public function __construct($skype = null)
	{
		$this->skype = $skype;
	}

	public function getSkype()
	{
		return $this->skype;
	}
}