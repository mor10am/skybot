<?php

/*
* This file is part of Skybot
*
* (c) 2012 Morten Amundsen <mor10am@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Skybot\Skype;

use Skybot\Skype\Reply;
use Skybot\Skype\AsyncMessage;
use Symfony\Component\EventDispatcher\Event;

class Message extends Event
{
    private $messageid;
    private $body;
    private $timestamp;
    private $skypename;
    private $chatid;
    private $marked = false;
    private $dm = false;
    private $dic;
    private $replybody;
    private $internal = false;

    public function __construct($msgid = null, $chatid = null, \Pimple $dic = null)
    {
        $this->messageid = $msgid;
        $this->chatid = $chatid;
        $this->dic = $dic;

        if ($msgid and $dic and isset($dic['skype'])) {
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

    public function setInteral()
    {
        $this->internal = true;
    }

    public function isInternal()
    {
        return $this->internal;
    }

    public function setDM()
    {
        $this->dm = true;
    }

    public function isDM()
    {
        return $this->dm;
    }

    public function getDic()
    {
        return $this->dic;
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

    public function getReplyBody()
    {
        return $this->replybody;
    }

    public function mark()
    {
        $this->marked = true;

        if ($this->messageid) {
            $this->dic['skype']->invoke("SET CHATMESSAGE {$this->messageid} SEEN");
        }
    }

    public function isMarked()
    {
        return $this->marked;
    }

    public function reply($txt)
    {
        $this->replybody = $txt;

        if (isset($this->dic['skype'])) {
            if ($txt instanceof Reply) {
                $this->dic['skype']->reply($txt);
            } else {
                $this->dic['skype']->reply(new Reply($this, $txt, $this->dm));
            }
        } else {
            echo $txt."\n";
        }

        return true;
    }

    public function createAsyncMessage()
    {
        $msg = new AsyncMessage();
        $msg->body = $this->body;
        $msg->chatid = $this->chatid;
        $msg->messageid = $this->messageid;
        $msg->timestamp = $this->timestamp;
        $msg->skypename = $this->skypename;

        return $msg;
    }
}