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

use Skybot\DriverInterface;
use Skybot\Config;
use Skybot\Storage;
use Skybot\PluginContainer;

use Skybot\Message\Chat;
use Skybot\Message\Reply;
use Skybot\Message\Direct;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Main extends EventDispatcher
{
    private $timestamp;

    private $botname;
    private $messages = array();
    private $calls = array();
    private $personalchats = array();
    private $chatnames = array();

    private $clients = array();
    private $socket;

    private $messages_served = 0;

    private $driver;
    private $config;
    private $log;
    private $plugincontainer;

    public function __construct(DriverInterface $driver, Config $config, \Monolog\Logger $log)
    {
        $this->driver = $driver;
        $this->config = $config;
        $this->log = $log;

    	$this->botname = $config->getSkypeName();
        $this->timestamp = time();

        $port = $config->getServerPort();

        if ($port and is_numeric($port)) {
            $this->socket = socket_create_listen($port);
            socket_set_nonblock($this->socket);
        } else {
            $port = "<NO PORT>";
        }

        $log->addInfo("Starting Skybot as ".$this->botname." and listening on port $port");
    }

    public function getBotName()
    {
        return $this->botname;
    }

    public function getStartupTime()
    {
        return $this->timestamp;
    }

    public function getMessagesServed()
    {
        return $this->messages_served;
    }

    public function setPluginContainer(PluginContainer $plugincontainer)
    {
        $this->plugincontainer = $plugincontainer;
    }

    public function getPluginContainer()
    {
        return $this->plugincontainer;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getDriver()
    {
        return $this->driver;
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
        $result = $this->driver->sendCommand("GET CHAT {$chatid} RECENTCHATMESSAGES");

        $recentmessages = explode(", ", str_replace("CHAT {$chatid} RECENTCHATMESSAGES ", "", $result));

        if (!count($recentmessages)) return true;

        $newmessages = array_diff($recentmessages, $this->messages);

        foreach ($newmessages as $msgid) {

            $this->messages[] = $msgid;

            $chatmsg = new Chat($msgid, $chatid, $this);

            if ($chatmsg->getSkypeName() == $this->config->getSkypeName() or $chatmsg->getTimestamp() < $this->timestamp or $chatmsg->isEmpty()) {
                continue;
            }

            $this->dispatch('skybot.message', $chatmsg);

            $chatmsg->mark();
        }
    }

    private function _getMissedChats()
    {
        $result = $this->driver->sendCommand("SEARCH MISSEDCHATS");

        $chats = explode(", ", substr($result, 6));

        return $chats;
    }

    private function _getRecentChats()
    {
        $result = $this->driver->sendCommand("SEARCH RECENTCHATS");

        $chats = explode(", ", substr($result, 6));

        return $chats;
    }

    public function isFriend($skypename)
    {
        return $this->driver->isContact($skypename);
    }

    public function waitLoop($millisec)
    {
        $this->driver->waitLoop($millisec);
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

            $this->log->addWarning("Message from $address : $txt");

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
                    $result = $this->driver->sendCommand("GET CHAT $cid FRIENDLYNAME");

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

            $msg = new Chat(null, $chatid, $this->dic);
            $msg->setBody($body);
            $msg->setSkypeName($skypename);
            $msg->setInternal();

            $this->dispatch('skybot.message', $msg);
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
            $this->driver->sendCommand("CHATMESSAGE ".$reply->getChatMsg()->getChatId()." ".$reply->getBody());
            $this->log->addInfo("Skybot reply to ".$reply->getChatMsg()->getDispName(). " : ".$reply->getBody());
        }
    }

    public function directMessage(DirectMessage $dm)
    {
        if (!isset($this->personalchats[$dm->getSkypeName()])) {
            $result = $this->driver->invoke("CHAT CREATE ".$dm->getSkypeName());

            $tmp = explode(' ', $result);

            if (isset($tmp[1])) {
                $chatid = $tmp[1];
                $this->personalchats[$dm->getSkypeName()] = $chatid;
            }
        } else {
            $chatid = $this->personalchats[$dm->getSkypeName()];
        }

        $this->driver->invoke("CHATMESSAGE ".$chatid." ".$dm->getBody());
        $this->log->addInfo("Skybot DM to ".$dm->getSkypeName(). " : ".$dm->getBody());
    }

    private function _refuseCalls()
    {
        $result = $this->driver->sendCommand("SEARCH CALLS");
        $calls = explode(", ", substr($result, 6));

        foreach ($calls as $callid) {
            if (!$callid) continue;

            if (isset($this->calls[$callid])) continue;
            $this->calls[$callid] = true;

            $result = $this->driver->sendCommand("GET CALL $callid STATUS");
            $t = explode(' ', $result);

            if (!isset($t[3])) continue;

            $status = $t[3];

            if ($status == 'RINGING') {
                $this->driver->sendCommand("ALTER CALL $callid END HANGUP");
            }
        }
    }
}
