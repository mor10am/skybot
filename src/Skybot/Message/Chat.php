<?php

/*
* This file is part of Skybot
*
* (c) 2012 Morten Amundsen <mor10am@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Skybot\Message;

use Skybot\Message\Reply;
use Skybot\Message\Async;
use Symfony\Component\EventDispatcher\Event;

class Chat extends Event
{
    private $messageid;
    private $body;
    private $timestamp;
    private $skypename;
    private $dispname;
    private $chatid;
    private $marked = false;
    private $dm = false;
    private $skybot;
    private $replybody;
    private $internal = false;

    public function __construct($msgid = null, $chatid = null, \Skybot\Main $skybot = null)
    {
        $this->messageid = $msgid;
        $this->chatid = $chatid;
        $this->skybot = $skybot;

        if ($msgid and $skybot) {
            $result = $skybot->getDriver()->sendCommand("GET CHATMESSAGE $msgid BODY");

            $template = "CHATMESSAGE $msgid BODY ";
            $this->setBody(trim(str_replace($template, "", $result)));

            $result = $skybot->getDriver()->sendCommand("GET CHATMESSAGE $msgid TIMESTAMP");

            $template = "CHATMESSAGE $msgid TIMESTAMP ";
            $this->timestamp = trim(str_replace($template, "", $result));

            $result = $skybot->getDriver()->sendCommand("GET CHATMESSAGE $msgid FROM_HANDLE");

            $template = "CHATMESSAGE $msgid FROM_HANDLE ";
            $this->setSkypeName(trim(str_replace($template, "", $result)));

            $result = $skybot->getDriver()->sendCommand("GET CHATMESSAGE $msgid FROM_DISPNAME");

            $template = "CHATMESSAGE $msgid FROM_DISPNAME ";
            $this->setDispName(trim(str_replace($template, "", $result)));
        }
    }

    public function setInternal()
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

    public function setSkybot(\Skybot\Main $skybot)
    {
        $this->skybot = $skybot;
    }

    public function getSkybot()
    {
        return $this->skybot;
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

    public function setDispName($name)
    {
        $this->dispname = $name;
    }

    public function getDispName()
    {
        if (!$this->dispname) return $this->getSkypeName();
        return $this->dispname;
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

        if ($this->messageid and $this->skybot) {
            $this->skybot->getDriver()->sendCommand("SET CHATMESSAGE {$this->messageid} SEEN");
        }
    }

    public function isMarked()
    {
        return $this->marked;
    }

    public function reply($txt)
    {
        $this->replybody = $txt;

        if ($this->skybot) {
            if ($txt instanceof Reply) {
                $this->skybot->reply($txt);
            } else {
                $this->skybot->reply(new Reply($this, $txt, $this->dm));
            }
        } else {
            echo $txt."\n";
        }

        return true;
    }

    public function createAsyncMessage()
    {
        $msg = new Async();
        $msg->body = $this->body;
        $msg->chatid = $this->chatid;
        $msg->messageid = $this->messageid;
        $msg->timestamp = $this->timestamp;
        $msg->skypename = $this->skypename;
        $msg->skybot = $this->skybot;

        return $msg;
    }
}