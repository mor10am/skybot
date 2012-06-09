<?php

namespace Skybot;

use Skybot\Skype\Message;

class Skype
{
    public $dbus;  
    public $proxy;

    public $timestamp;
    public $botname;  
    public $eventemitter;
    public $config;
    public $messages = array();
    public $marked = array();
    
    public function __construct($config, $eventemitter)
    {
        $this->dbus = new \DBus(\Dbus::BUS_SESSION, true);
        $this->proxy = $this->dbus->createProxy("com.Skype.API", "/com/Skype", "com.Skype.API");

        $this->proxy->Invoke("NAME SKYBOT");
        $this->proxy->Invoke("PROTOCOL 7");

    	$this->botname = $config->getSkypeName();
        $this->eventemitter = $eventemitter;
        $this->config = $config;
        $this->timestamp = time();
    }
    
    public function getEventEmitter()
    {
        return $this->eventemitter;
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

            $msg = new Message($msgid, $chatid, $this);
            
            if ($msg->getSkypeName() == $this->config->getSkypeName() or $msg->getTimestamp() <= $this->timestamp or $msg->isEmpty()) {
                continue;
            }            

            $this->eventemitter->emit('skype.message', array($msg));

            $msg->mark();
            $this->marked[$msgid] = true;            
        }         
    }   

    public function waitLoop($millisec)
    {
        $this->dbus->waitLoop($millisec);
    } 
}
