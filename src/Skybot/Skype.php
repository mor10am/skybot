<?php

namespace Skybot;

use Skybot\Skype\Chat;
use Skybot\Skype\Message;

class Skype
{
    public $timestamp;
    public $botname;    
    public $proxy;
    public $chats = array();
    
    public function __construct($botname, $proxy)
    {
    	$this->botname = $botname;
        $this->proxy = $proxy;
        $this->timestamp = time();
    }
    
    public function getProxy()
    {
        return $this->proxy;
    }       
       
    public function invoke($command)
    {
    	return $this->proxy->Invoke($command);    	
    }

    public function getRecentMessages()
    {
        $this->getRecentChats();
            
        $messages = array();

        foreach ($this->chats as $chat) {    
            $messages = array_merge($messages, $chat->getMessages($this->botname, $this->timestamp));
        }
        
        return $messages;
    }    
    
    public function getRecentChats()
    {
        $result = $this->invoke("SEARCH RECENTCHATS");
        
        $this->_returnChatList($result);
        
        return $this->chats;
    }

    private function _returnChatList($chatstring)
    {
        $chats = explode(", ", substr($chatstring, 6));

        $chatlist = array();
        
        foreach ($chats as $chatid) {
            if (isset($this->chats[$chatid])) continue;

            //if (strpos($chatid, $this->botname) === false) continue;

            $this->chats[$chatid] = new Chat($chatid, $this);
        }
    }
}
