<?php
require "aws/aws-autoloader.php";
require "../../cred.php";
use Aws\DynamoDb\DynamoDbClient;
class DatabaseControl {


  function getStationsByZone($zone) {
     $client = DynamoDbClient::factory(array(
      'key'    => $aws_key,
      'secret' => $aws_secret,
      'region' => 'eu-west-1'
    ));
    $response = $client->getItem(array(
      'TableName' => 'station_info',
      'Key' => array(
          'london_zone' => array( 'S' => $zone )
        )
      ));
      return $response;
  }
}
?>
