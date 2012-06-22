<?php

namespace Skybot;

use Skybot\Skype\Message;

interface FilterInterface
{
	function handle(Message $chatmsg);
}