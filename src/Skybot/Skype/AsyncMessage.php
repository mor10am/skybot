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
    public $dic;

    public function reply(Reply $reply)
    {
        $port = $this->dic['config']->getServerPort();
        
        if (!$port) return false;

        if (!$skypename = $reply->getSkypeName()) {
            $skypename = 'async';
        }

        $txt = "[".$reply->getChatId()."][".$skypename."] echo ".$reply->getBody();

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

    public function getSkypeName()
    {
        return $this->skypename;
    }

    public function getChatId()
    {
        return $this->chatid;
    }
}

