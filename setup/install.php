<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
require "../src/aws/aws-autoloader.php";
use Aws\DynamoDb\DynamoDbClient;
$client = DynamoDbClient::factory(array(
  'profile' => 'default',
  'region' => 'eu-west-1',
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
for($i=1;$i<count($stationName)+1;$i++) {
  $result = $client->putItem(array(
    'TableName' => 'station_info',
    'Item' => array(
          'station_id'      => array('S' => $i),
          'station_name' => array('S' => $stationName[$i]),
          'map_x' => array('N' => $mapX[$i]),
          'map_y' => array('N' => $mapY[$i]),
          'latitude' => array('N' => $latitude[$i]),
          'longitude' => array('N' => $longitude[$i]),
          'london_zone' => array('N' => $londonZone[$i]),
          'post_code' => array('S' => $postCode[$i])
      )
  ));
}

?>
