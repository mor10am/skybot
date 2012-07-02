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

use Skybot\Main;
use Skybot\Message\Chat;

interface FilterInterface
{
	function __construct(Main $skybot);
	function handle(Chat $chatmsg);
}