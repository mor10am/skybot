<?php

namespace Skybot;

use Skybot\Main;
use Skybot\Message\Chat;
use Skybot\Message\Direct;
use Skybot\Message\Reply;

interface DriverInterface
{
	function __construct();
	function initialize($params = array());
	function refuseCalls();
	function getRecentChats();
	function getMissedChats();
	function getRecentMessagesForChat($chatid, Main $skybot);
	function isContact($name);
	function getContacts();
	function getMessageProperties($msgid);
	function getChatProperty($chatid, $property);
	function sendDirectMessage(Direct $dm);
	function sendReply(Reply $reply);
	function createChatWith($contactname);
	function markSeen(Chat $chatmsg);
}