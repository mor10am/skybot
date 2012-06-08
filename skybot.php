<?php

$loader = require_once 'vendor/autoload.php';

$dbus = new DBus(Dbus::BUS_SESSION, true);
$proxy = $dbus->createProxy("com.Skype.API", "/com/Skype", "com.Skype.API");

$proxy->Invoke("NAME SKYBOT");
$proxy->Invoke("PROTOCOL 7");

$eventemitter = new Evenement\EventEmitter();

$skype = new \Skybot\Skype('morten_amundsen', $proxy, $eventemitter);

$plugins = new \Skybot\PluginContainer($eventemitter, $skype);

do {
    $skype->searchAndEmitChatMessages();

    $dbus->waitLoop(1000);
} while(true);
