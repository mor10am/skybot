<?php

namespace Skybot\Skype;

use Skybot\Skype\Message;

class Chat
{
    public $chatid;
    public $messages = array();
    public $marked = array();
    public $skype;

    public function __construct($chatid, $skype)
    {
        $this->chatid = $chatid;
        $this->skype = $skype;
    }
    
    public function getId()
    {
        return $this->chatid;
    }
    
    public function getSkype()
    {
        return $this->skype;
    }
    
    public function getMessages($botname, $timestamp)
    {
        $result = $this->skype->invoke("GET CHAT {$this->chatid} RECENTCHATMESSAGES"); 
        
        $template = "CHAT {$this->chatid} RECENTCHATMESSAGES ";
        $messages = explode(", ", str_replace($template, "", $result));
        
        foreach ($messages as $msgid) {
            if (isset($this->messages[$msgid])) continue;
            if (isset($this->marked[$msgid])) continue;

            $msg = new Message($this, $msgid);
            
            if ($msg->getHandle() == $botname or $msg->getTimestamp() <= $timestamp or $msg->isEmpty()) {
                $msg->mark();
                $this->marked[$msgid] = true;
                continue;
            }            

            $this->messages[$msgid] = $msg;
        }

        return $this->messages;
    }     

    public function chat($message)
    {
        $this->skype->invoke("CHATMESSAGE {$this->chatid} ".$message);
    }  
}