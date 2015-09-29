<?php
die("<h1>Setup has already run");
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
require "../src/aws/aws-autoloader.php";
require "../../cred.php";
use Aws\DynamoDb\DynamoDbClient;
$client = DynamoDbClient::factory(array(
  'key'    => $aws_key,
  'secret' => $aws_secret,
  'region' => 'eu-west-1'
));


$row = 1;
if (($handle = fopen("../stations_locations.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $stationName[] = $data[0];
        $mapX[] = $data[1];
        $mapY[] = $data[2];
        $latitude[] = $data[3];
        $longitude[] = $data[4];
        $londonZone[] = $data[5];
        $postCode[] = $data[6];
      $row++;
    }
    fclose($handle);
}
for($i=133;$i<count($stationName);$i++) {
  echo "<p>" . $i . ": " . $stationName[$i] . "</p>";
  $result = $client->putItem(array(
    'TableName' => 'station_info',
    'Item' => array(
          'station_id'      => array('S' => (string)$i),
          'station_name' => array('S' => $stationName[$i]),
          'map_x' => array('S' => (string)$mapX[$i]),
          'map_y' => array('S' => (string)$mapY[$i]),
          'latitude' => array('S' => (string)$latitude[$i]),
          'longitude' => array('S' => (string)$longitude[$i]),
          'london_zone' => array('S' => $londonZone[$i]),
          'post_code' => array('S' => $postCode[$i])
      )
  ));
}

?>
