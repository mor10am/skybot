<?php

$loader = require_once __DIR__."/../vendor/autoload.php";
$loader->add('Skybot', __DIR__."/../src/");

$plugin = new \Skybot\Plugin\SMS();

$message = new \Skybot\Skype\Message();

$message->setBody("sms 98427456 test");
$message->setHandle("tpnordic.hubot");

xdebug_var_dump($plugin->parse($message));