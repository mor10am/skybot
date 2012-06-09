<?php

$loader = require_once __DIR__."/../vendor/autoload.php";

use Skybot\PluginContainer;

$plugin = new \Skybot\Plugin\ListPlugins();
$plugin2 = new \Skybot\Plugin\Ping();

$dic = new \Pimple();
$plugincontainer = new PluginContainer($dic);
$plugincontainer->add($plugin);
$plugincontainer->add($plugin2);
$dic['plugincontainer'] = $plugincontainer;

$message = new \Skybot\Skype\Message(null, null, $dic);

$message->setBody("plugins");
$message->setSkypeName("myskypename");

$plugin->parse($message);

xdebug_var_dump($plugin->getResult());