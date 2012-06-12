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

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if (!socket_connect($socket, '127.0.0.1', $port)) {
            $this->dic['log']->addError(socket_strerror(socket_last_error()));
            return false;
        }

        socket_write($socket, $txt, strlen($txt));
        socket_close($socket);
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

