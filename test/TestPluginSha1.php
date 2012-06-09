<?php

$loader = require_once __DIR__."/../vendor/autoload.php";

$plugin = new \Skybot\Plugin\Sha1();

$message = new \Skybot\Skype\Message();

$message->setBody("sha1 test");
$message->setSkypeName("myskypename");

$plugin->parse($message);

xdebug_var_dump($plugin->getResult());