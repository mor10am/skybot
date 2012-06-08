<?php

require_once "../src/PluginContainer.php";
require_once "../src/Skype/Message.php";

$plugin = new Plugin_CDK();

$message = new Skype_Message();

$message->setBody("cdk 20601391");
$message->setHandle("tpnordic.hubot");

xdebug_var_dump($plugin->parse($message));