<?php

namespace Skybot;

use Skybot\Skype\Message;

class Skype
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
    
    public function getEventEmitter()
    {
        return $this->dic['eventemitter'];
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

            $this->dic['eventemitter']->emit('skype.message', array($msg));

            $msg->mark();
            $this->marked[$msgid] = true;            
        }         
    }   

    public function waitLoop($millisec)
    {
        $this->dbus->waitLoop($millisec);
    } 

    public function reply(Message $message, $txt, $dm = false)
    {
        if ($dm) {
            $this->directMessage($message, $txt);
        } else {
            $this->dic['skype']->invoke("CHATMESSAGE ".$message->getChatId()." ".$txt);
            $this->dic['log']->addInfo("Skybot reply to ".$message->getSkypeName(). " : ".$txt);
        }
    }

    public function directMessage(Message $message, $txt)
    {
        if (!isset($this->personalchats[$message->getSkypeName()])) {
            $result = $this->dic['skype']->invoke("CHAT CREATE ".$message->getSkypeName());

            $tmp = explode(' ', $result);

            if (isset($tmp[1])) {
                $chatid = $tmp[1];
                $this->personalchats[$message->getSkypeName()] = $chatid;
            }
        } else {
            $chatid = $this->personalchats[$message->getSkypeName()];
        }

        $this->dic['skype']->invoke("CHATMESSAGE ".$chatid." ".$txt);
        $this->dic['log']->addInfo("Skybot DM to ".$message->getSkypeName(). " : ".$txt);
    }
}
