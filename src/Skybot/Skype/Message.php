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
    private $dic;

    public function __construct($msgid = null, $chatid = null, \Pimple $dic = null)
    {
        $this->messageid = $msgid;
        $this->chatid = $chatid;
        $this->dic = $dic;

        if ($dic and $dic['skype']) {
            $result = $dic['skype']->invoke("GET CHATMESSAGE $msgid BODY");

            $template = "CHATMESSAGE $msgid BODY ";
            $this->setBody(trim(str_replace($template, "", $result)));

            $result = $dic['skype']->invoke("GET CHATMESSAGE $msgid TIMESTAMP");

            $template = "CHATMESSAGE $msgid TIMESTAMP ";
            $this->timestamp = trim(str_replace($template, "", $result));        

            $result = $dic['skype']->invoke("GET CHATMESSAGE $msgid FROM_HANDLE");

            $template = "CHATMESSAGE $msgid FROM_HANDLE ";
            $this->setSkypeName(trim(str_replace($template, "", $result)));  
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
        $this->dic['skype']->invoke("SET CHATMESSAGE {$this->messageid} SEEN");        
    }

    public function isMarked()
    {
        return $this->marked;
    }

    public function reply($txt)
    {
        if ($this->dic['skype']) {
            $this->dic['skype']->invoke("CHATMESSAGE {$this->chatid} ".$txt);
        }

        $this->dic['log']->addInfo("Skybot to ".$this->getSkypeName(). " : ".$txt);
    }
}