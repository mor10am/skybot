<?php

namespace Skybot;

use Skybot\Skype\Message;
use Skybot\Skype\Reply;
use Skybot\Skype\DirectMessage;

use Evenement\EventEmitter;

class Skype extends EventEmitter
{
    public $dbus;  
    public $proxy;

    public $timestamp;
    public $botname;  
    public $messages = array();
    public $marked = array();
    
    public $personalchats = array();

    public function __construct($dic)
    {
        $this->dic = $dic;
        $this->dbus = new \DBus(\Dbus::BUS_SESSION, true);
        $this->proxy = $this->dbus->createProxy("com.Skype.API", "/com/Skype", "com.Skype.API");

        $this->proxy->Invoke("NAME SKYBOT");
        $this->proxy->Invoke("PROTOCOL 7");

    	$this->botname = $dic['config']->getSkypeName();
        $this->timestamp = time();

        $dic['log']->addInfo("Starting Skybot as ".$this->botname);
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
        $result = $this->invoke("SEARCH RECENTCHATS");

        $chats = explode(", ", substr($result, 6));

        if (!count($chats)) return true;

        foreach ($chats as $chatid) {
            $this->loadAndEmitChatMessages($chatid);
        }
    }    

    private function loadAndEmitChatMessages($chatid)
    {
        $result = $this->invoke("GET CHAT {$chatid} RECENTCHATMESSAGES"); 
        
        $messages = explode(", ", str_replace("CHAT {$chatid} RECENTCHATMESSAGES ", "", $result));
        
        if (!count($messages)) return true;

        foreach ($messages as $msgid) {
            if (isset($this->messages[$msgid]) or isset($this->marked[$msgid])) continue;

            $this->messages[$msgid] = true;

            $msg = new Message($msgid, $chatid, $this->dic);
            
            if ($msg->getSkypeName() == $this->dic['config']->getSkypeName() or $msg->getTimestamp() <= $this->timestamp or $msg->isEmpty()) {
                continue;
            }            

            $this->emit('skype.message', array($msg));

            $msg->mark();
            $this->marked[$msgid] = true;            
        }         
    }   

    public function waitLoop($millisec)
    {
        $this->dbus->waitLoop($millisec);
    } 

    public function reply(Reply $reply)
    {
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
}
