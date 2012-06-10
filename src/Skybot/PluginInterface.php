<?php

namespace Skybot;

use Skybot\Skype\Message;

interface PluginInterface
{
	function handle($result, $chatmsg);
}