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

use Skybot\Skype\Message;
use Skybot\Skype\Reply;
use Skybot\Skype\DirectMessage;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Skype extends EventDispatcher
{
    public $dbus;
    public $proxy;

    public $timestamp;

    public $botname;
    public $messages = array();
    public $calls = array();
    public $personalchats = array();
    public $chatnames = array();

    private $clients = array();
    private $socket;

    public $messages_served = 0;

    public function __construct($dic)
    {
        $this->dic = $dic;

        $this->dbus = new \DBus(\Dbus::BUS_SESSION, true);
        $this->proxy = $this->dbus->createProxy("com.Skype.API", "/com/Skype", "com.Skype.API");

        $this->proxy->Invoke("NAME SKYBOT");
        $this->proxy->Invoke("PROTOCOL 7");

    	$this->botname = $dic['config']->getSkypeName();
        $this->timestamp = time();

        $port = $dic['config']->getServerPort();

        if ($port and is_numeric($port)) {
            $this->socket = socket_create_listen($port);
            socket_set_nonblock($this->socket);
        } else {
            $port = "<NO PORT>";
        }

        $dic['log']->addInfo("Starting Skybot as ".$this->botname." and listening on port $port");
    }

    public function getProxy()
    {
        return $this->proxy;
    }

    public function invoke($command)
    {
        //echo $command."\n";
    	$response = $this->proxy->Invoke($command);
        //echo $response."\n";
        return $response;
    }

    public function handleChatMessages()
    {
        $this->_refuseCalls();

        $this->_getMessagesFromPort();

        $chats = $this->_getMissedChats();

        if (!count($chats)) return false;

        foreach ($chats as $chatid) {
            if (!$chatid) continue;
            $this->loadAndEmitChatMessages($chatid);
        }
    }

    private function loadAndEmitChatMessages($chatid)
    {
        $result = $this->invoke("GET CHAT {$chatid} RECENTCHATMESSAGES");

        $recentmessages = explode(", ", str_replace("CHAT {$chatid} RECENTCHATMESSAGES ", "", $result));

        if (!count($recentmessages)) return true;

        $newmessages = array_diff($recentmessages, $this->messages);

        foreach ($newmessages as $msgid) {

            $this->messages[] = $msgid;

            $chatmsg = new Message($msgid, $chatid, $this->dic);

            if ($chatmsg->getSkypeName() == $this->dic['config']->getSkypeName() or $chatmsg->getTimestamp() < $this->timestamp or $chatmsg->isEmpty()) {
                continue;
            }

            $this->dispatch('skype.message', $chatmsg);

            $chatmsg->mark();
        }
    }

    private function _getMissedChats()
    {
        $result = $this->invoke("SEARCH MISSEDCHATS");

        $chats = explode(", ", substr($result, 6));

        return $chats;
    }

    private function _getRecentChats()
    {
        $result = $this->invoke("SEARCH RECENTCHATS");

        $chats = explode(", ", substr($result, 6));

        return $chats;
    }

    public function isFriend($skypename)
    {
        $result = $this->invoke("SEARCH FRIENDS");
        $friends = explode(", ", substr($result, 6));

        if (!count($friends)) return false;

        return (in_array($skypename, $friends));
    }

    public function waitLoop($millisec)
    {
        $this->dbus->waitLoop($millisec);
    }

    private function _getMessagesFromPort()
    {
        if (!is_resource($this->socket)) return true;

        if (($client = @socket_accept($this->socket)) !== false) {
            $this->clients[] = $client;
        }

        if (!count($this->clients)) return true;

        $r = $this->clients;
        $w = NULL;
        $e = $this->clients;

        socket_select($r, $w, $e, 0, 500);

        if (count($e)) {
            $this->clients = array_diff($this->clients, $e);
        }

        foreach ($r as $client) {
            $bytes = socket_recv($client, $txt, 1024, MSG_DONTWAIT);
            if (!$bytes) continue;

            socket_getpeername($client, $address, $port);

            if ($this->dic['log']) {
                $this->dic['log']->addWarning("Message from $address : $txt");
            }

            if (!$matches = self::parseTcpMessage($txt)) continue;

            $chatname = strtolower(trim($matches[1]));
            $skypename = trim($matches[2]);
            $body = $matches[3];

            if (substr($chatname, 0, 1) == '#') {
                $this->chatnames[$chatname] = $chatname;
            }

            if (isset($this->chatnames[$chatname])) {
                $chatid = $this->chatnames[$chatname];
            } else {
                $chats = $this->_getRecentChats();

                if (!count($chats)) continue;

                $chatid = false;

                foreach ($chats as $cid) {
                    $result = $this->invoke("GET CHAT $cid FRIENDLYNAME");

                    $friendlyname = strtolower(trim(str_replace("CHAT $cid FRIENDLYNAME", "", $result)));

                    $this->chatnames[$friendlyname] = $cid;

                    if (strpos($friendlyname, $chatname) !== false) {
                        $this->chatnames[$chatname] = $cid;
                        $chatid = $cid;
                        break;
                    }
                }

                if (!$chatid) continue;
            }

            $msg = new Message(null, $chatid, $this->dic);
            $msg->setBody($body);
            $msg->setSkypeName($skypename);
            $msg->setInternal();

            $this->dispatch('skype.message', $msg);
        }
    }

    public static function parseTcpMessage($txt)
    {
        if (!preg_match("/\[(.{1,})?\]\[(\w{1,})?\]\s(.*)$/ms", $txt, $matches)) return false;

        return $matches;
    }

    public function reply(Reply $reply)
    {
        $this->messages_served++;

        if ($reply->isDM()) {
            $this->directMessage($reply->createDirectMessage());
        } else {
            $this->dic['skype']->invoke("CHATMESSAGE ".$reply->getChatMsg()->getChatId()." ".$reply->getBody());
            $this->dic['log']->addInfo("Skybot reply to ".$reply->getSkypeName(). " : ".$reply->getBody());
        }
    }

    public function directMessage(DirectMessage $dm)
    {
        if (!isset($this->personalchats[$dm->getSkypeName()])) {
            $result = $this->dic['skype']->invoke("CHAT CREATE ".$dm->getSkypeName());

            $tmp = explode(' ', $result);

            if (isset($tmp[1])) {
                $chatid = $tmp[1];
                $this->personalchats[$dm->getSkypeName()] = $chatid;
            }
        } else {
            $chatid = $this->personalchats[$dm->getSkypeName()];
        }

        $this->dic['skype']->invoke("CHATMESSAGE ".$chatid." ".$dm->getBody());
        $this->dic['log']->addInfo("Skybot DM to ".$dm->getSkypeName(). " : ".$dm->getBody());
    }

    private function _refuseCalls()
    {
        $result = $this->invoke("SEARCH CALLS");
        $calls = explode(", ", substr($result, 6));

        foreach ($calls as $callid) {
            if (isset($this->calls[$callid])) continue;
            $this->calls[$callid] = true;

            $result = $this->invoke("GET CALL $callid STATUS");
            $t = explode(' ', $result);

            $status = $t[3];

            if ($status == 'RINGING') {
                $this->invoke("ALTER CALL $callid END HANGUP");
            }
        }
    }
}
