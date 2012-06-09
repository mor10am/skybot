<?php

namespace Skybot\Skype;

class Message
{
    private $messageid;
    private $body;
    private $timestamp;
    private $skypename;
    private $chatid;
    private $marked = false;
    private $skype;

    public function __construct($msgid = null, $chatid = null, $skype = null)
    {
        $this->messageid = $msgid;
        $this->chatid = $chatid;
        $this->skype = $skype;

        if ($skype) {
            $result = $skype->invoke("GET CHATMESSAGE $msgid BODY");

            $template = "CHATMESSAGE $msgid BODY ";
            $this->body = trim(str_replace($template, "", $result));

            $result = $skype->invoke("GET CHATMESSAGE $msgid TIMESTAMP");

            $template = "CHATMESSAGE $msgid TIMESTAMP ";
            $this->timestamp = trim(str_replace($template, "", $result));        

            $result = $skype->invoke("GET CHATMESSAGE $msgid FROM_HANDLE");

            $template = "CHATMESSAGE $msgid FROM_HANDLE ";
            $this->skypename = trim(str_replace($template, "", $result));  
        }      
    }

    public function getChatId()
    {
        return $this->chatid;
    }

    public function setBody($msg)
    {
        $this->body = $msg;
    }

    public function setSkypeName($handle)
    {
        $this->skypename = $handle;
    }

    public function getSkypeName()
    {
        return $this->skypename;        
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }
    
    public function getSkype()
    {
        return $this->skype;
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
        $this->skype->invoke("SET CHATMESSAGE {$this->messageid} SEEN");        
    }

    public function isMarked()
    {
        return $this->marked;
    }

    public function reply($msg)
    {
        if ($this->skype) {
            $this->skype->invoke("CHATMESSAGE {$this->chatid} ".$msg);
        } else {
            echo $msg."\n";
        }
    }
}