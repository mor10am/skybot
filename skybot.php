<?php

$loader = require_once 'vendor/autoload.php';
$loader->add('Skybot', __DIR__."/src/");

$dbus = new DBus(Dbus::BUS_SESSION, true);
$proxy = $dbus->createProxy("com.Skype.API", "/com/Skype", "com.Skype.API");

$proxy->Invoke("NAME SKYBOT");
$proxy->Invoke("PROTOCOL 7");

$skype = new \Skybot\Skype('morten_amundsen', $proxy);

$plugins = new \Skybot\PluginContainer();

do {
    $plugins->handle($skype->getRecentMessages());

    $dbus->waitLoop(1000);
} while(true);
