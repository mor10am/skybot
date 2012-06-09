<?php

$loader = require_once __DIR__."/../vendor/autoload.php";

$plugin = new \Skybot\Plugin\Ping();

$message = new \Skybot\Skype\Message();

$message->setBody("ping me");
$message->setSkypeName("myskypename");

$plugin->parse($message);

xdebug_var_dump($plugin->getResult());