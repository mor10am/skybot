<?php

namespace Skybot;

use Skybot\Skype\Message;

class Skype
{
    public $timestamp;
    public $botname;    
    public $proxy;
    public $eventemitter;
    public $messages = array();
    public $marked = array();
    
    public function __construct($botname, $proxy, $eventemitter)
    {
    	$this->botname = $botname;
        $this->proxy = $proxy;
        $this->eventemitter = $eventemitter;
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

    public function searchAndEmitChatMessages()
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
            
            if ($msg->getHandle() == $this->botname or $msg->getTimestamp() <= $this->timestamp or $msg->isEmpty()) {
                continue;
            }            

            $this->eventemitter->emit('skype.message', array($msg));

            $msg->mark();
            $this->marked[$msgid] = true;            
        }         
    }    
}
