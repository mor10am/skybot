<?php

namespace Skybot\Skype;

class AsyncMessage
{
    public $messageid;
    public $body;
    public $timestamp;
    public $skypename;
    public $chatid;
    public $result;
    public $plugin;
    public $dm;
    public $dic;

    public function reply($txt)
    {
        $port = $this->dic['config']->getServerPort();
        
        if (!$port) return false;

        if (!$this->skypename) {
            $this->skypename = 'async';
        }

        $txt = "[".$this->chatid."][".$this->skypename."] echo ".$txt;

        if (!$socket = fsockopen('127.0.0.1', $port, $errno, $errstr, 2)) {
            $this->dic['log']->addError($errstr);
            return false;
        }

        fwrite($socket, $txt, strlen($txt));
        fflush($socket);
        fclose($socket);

        $socket = null;

        return true;         
    }

    public function setDM()
    {
        $this->dm = true;
    }

    public function isDM()
    {
        return $this->dm;
    }    

    public function getSkypeName()
    {
        return $this->skypename;
    }

    public function getChatId()
    {
        return $this->chatid;
    }

    public function getBody()
    {
        return $this->body;
    }
}

