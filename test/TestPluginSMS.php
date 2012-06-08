<?php

$loader = require_once __DIR__."/../vendor/autoload.php";

$plugin = new \Skybot\Plugin\SMS();

$message = new \Skybot\Skype\Message();

$message->setBody("sms 98427456 test");
$message->setHandle("tpnordic.hubot");

xdebug_var_dump($plugin->parse($message));