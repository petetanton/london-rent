<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
include 'src/DatabaseControl';
$zooplaKey = getenv("ZOOPLA_KEY");
$dbControl = new DatabaseControl();
var_dump($dbControl->getStationsByZone("1"));

?>
