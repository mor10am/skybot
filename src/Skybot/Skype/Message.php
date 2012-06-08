<?php

namespace Skybot\Skype;

class Message
{
    private $messageid;
    private $body;
    private $timestamp;
    private $handle;
    private $marked = false;
    private $chat;

    public function __construct($chat = null, $msgid = null)
    {
        if ($chat and $msgid) {
            $this->chat = $chat;
            $this->messageid = $msgid;
            $msg = $chat->getSkype()->invoke("GET CHATMESSAGE $msgid BODY");

            $template = "CHATMESSAGE $msgid BODY ";
            $this->body = trim(str_replace($template, "", $msg));

            $msg = $chat->getSkype()->invoke("GET CHATMESSAGE $msgid TIMESTAMP");

            $template = "CHATMESSAGE $msgid TIMESTAMP ";
            $this->timestamp = trim(str_replace($template, "", $msg));        

            $msg = $chat->getSkype()->invoke("GET CHATMESSAGE $msgid FROM_HANDLE");

            $template = "CHATMESSAGE $msgid FROM_HANDLE ";
            $this->handle = trim(str_replace($template, "", $msg));     
        }

        echo "[".date('H:i:s', $this->timestamp)."] {$this->handle}: {$this->body}\n";
    }

    public function setBody($msg)
    {
        $this->body = $msg;
    }

    public function setHandle($handle)
    {
        $this->handle = $handle;
    }

    public function getHandle()
    {
        return $this->handle;        
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }
    
    public function getChat()
    {
        return $this->chat;
    }

    public function isEmpty()
    {
        return (strlen($this->body) == 0);
    }
    
    public function getBody()
    {
        return $this->body;
    }
    
    public function mark()
    {
        $this->marked = true;
        $this->chat->getSkype()->invoke("SET CHATMESSAGE {$this->messageid} SEEN");        
    }

    public function isMarked()
    {
        return $this->marked;
    }

    public function reply($msg)
    {
        if ($this->chat) {
            $this->chat->chat($msg);
        } else {
            echo $msg."\n";
        }
    }
}