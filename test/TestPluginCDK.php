<?php

$loader = require_once __DIR__."/../vendor/autoload.php";

$plugin = new \Skybot\Plugin\CDK();

$message = new \Skybot\Skype\Message();

$message->setBody("cdk 20601391");
$message->setHandle("tpnordic.hubot");

xdebug_var_dump($plugin->parse($message));