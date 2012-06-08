<?php

class Plugin_Ping extends BasePlugin implements PluginInterface
{
	public function parse(Skype_Message $message)
	{
		if (!$matches = preg_match("/^ping$/", $message->getBody())) return false;
		
		$message->reply("pong");			

		return true;
	}	
}