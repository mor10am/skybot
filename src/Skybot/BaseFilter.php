<?php

/*
* This file is part of Skybot
*
* (c) 2012 Morten Amundsen <mor10am@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Skybot;

use Skybot\Skype;
use Skybot\Skype\Message;

abstract class BaseFilter
{
	const DISABLED = 0;
	const BEFORE_PLUGINS = 1;
	const AFTER_PLUGINS = 2;

	public $pri = 0;
	public $pos = self::DISABLED;
	public $description;

	public function getPri()
	{
		return $this->pri;
	}

	public function beforePlugins()
	{
		return $this->pos == self::BEFORE_PLUGINS;
	}

	public function afterPlugins()
	{
		return $this->pos == self::AFTER_PLUGINS;
	}

	public function getDescription()
	{
		return $this->description;
	}
}