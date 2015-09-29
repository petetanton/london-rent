<?php
include '/Users/tantop01/phpsite.php';
$zooplaKey = getenv("ZOOPLA_KEY");

$row = 1;
if (($handle = fopen("stations_locations.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
      if($data[5] == 1 && $data[0] == "Elephant and Castle") {
        $stationName[] = $data[0];
        $mapX[] = $data[1];
        $mapY[] = $data[2];
        $latitude[] = $data[3];
        $longitude[] = $data[4];
        $londonZone[] = $data[5];
        $postCode[] = $data[6];
      }
      $row++;
    }
    fclose($handle);
}
print_r($stationName);

echo "THIS IS A TEST";
?>
