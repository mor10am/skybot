<?php

require_once 'Skype/Chat.php';
require_once 'Skype/Message.php';

class Skype
{
    public $proxy;
    public $chats = array();
    public $timestamp;
    public $botname;
    
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
    	echo "$command\n";
    	$result = $this->proxy->Invoke($command);
    	echo $result."\n\n";
    	return $result;
    }

    public function getRecentMessages()
    {
        $this->getRecentChats();
            
        $messages = array();

        foreach ($this->chats as $chat) {    
            $messages = $chat->getMessages($this->botname, $this->timestamp);
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

            if (strpos($chatid, 'hubot') === false) continue;

            $this->chats[$chatid] = new Skype_Chat($chatid, $this);
        }                
    }    
}
