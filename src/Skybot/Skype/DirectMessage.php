<?php

/*
* This file is part of Skybot
*
* (c) 2012 Morten Amundsen <mor10am@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

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