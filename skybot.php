<?php

require_once 'src/Skype.php';
require_once 'src/PluginContainer.php';

$dbus = new DBus(Dbus::BUS_SESSION, true);
$proxy = $dbus->createProxy( "com.Skype.API", "/com/Skype", "com.Skype.API");

$proxy->Invoke("NAME SKYBOT");
$proxy->Invoke("PROTOCOL 7");

$skype = new Skype('morten_amundsen', $proxy);

$plugins = new PluginContainer();

do {
    $plugins->handle($skype->getRecentMessages());

    $result = $dbus->waitLoop(1000);
} while(true);

