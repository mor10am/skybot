<?php

interface PluginInterface
{
	function parse(Skype_Message $message);
}