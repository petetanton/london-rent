<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
require "src/aws/aws-autoloader.php";
require "../cred.php";
use Aws\DynamoDb\DynamoDbClient;
$zooplaKey = getenv("ZOOPLA_KEY");
$client = DynamoDbClient::factory(array(
 'key'    => $aws_key,
 'secret' => $aws_secret,
 'region' => 'eu-west-1'
));
$response = $client->getItem(array(
 'TableName' => 'station_info',
 'Key' => array(
     'london_zone' => array( 'S' => "1" )
   )
 ));
var_dump($response);

?>
