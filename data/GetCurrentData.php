<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
date_default_timezone_set("Europe/London");
require "../src/aws/aws-autoloader.php";
require "../../cred.php";
use Aws\DynamoDb\DynamoDbClient;
$client = DynamoDbClient::factory(array(
    'key'    => $aws_key,
    'secret' => $aws_secret,
    'region' => 'eu-west-1'
));
$dbStationInfo = $client->scan(array('TableName' => 'station_info'));
$dbPropertyListings = $client->scan(array('TableName' => 'property_listings'));


foreach ($dbStationInfo['Items'] as $item) {
    if(strpos($item['london_zone']['S'],'1' ) === 0 && isset($item['last_updated']['S'])) {
        $stations_stationName[] = $item['station_name']['S'];
        $stations_postCode[] = $item['post_code']['S'];
        $stations_londonZone[] = $item['london_zone']['S'];
        $stations_stationId[] = $item['station_id']['S'];
        if (isset($item['last_updated'])) {
            $stations_lastUpdate[] = $item['last_updated']['S'];
        } else {
            $stations_lastUpdate[] = 0;
        }
    }
}
if(!isset($stations_postCode)) { header("ContentType: xml/text"); die("<error><info>404: No data found</info></error>");}
header("Content-type: text/xml");
echo '<stations>';
foreach ($dbPropertyListings['Items'] as $item) {
    $listing_listingId[] = $item['listing_id']['S'];
    $listing_noBeds[] = $item['no_bed']['S'];
    $listing_rentMonth[] = $item['price_rent_month']['S'];
    $listing_stationId[] = $item['station_id']['N'];
}
for($i=0;$i<count($stations_postCode);$i++) {
    echo '<station>';
    echo '<stationId>' . $stations_stationId[$i] . '</stationId>';
    echo '<stationName>' . $stations_stationName[$i] . '</stationName>';
    echo '<stationLondonZone>' . $stations_londonZone[$i] . '</stationLondonZone>';
    echo '<stationPostCode>' . $stations_postCode[$i] . '</stationPostCode>';
    $noOfProperties = 0;
    $totalMonth = 0;
    for($j=0; $j<count($listing_listingId); $j++) {
        if($listing_stationId[$j] == $stations_stationId[$i]){
            $noOfProperties++;
            $totalMonth = $totalMonth + $listing_rentMonth[$i];
        }
    }
    echo '<noOfListings>' . $noOfProperties . '</noOfListings>';
    echo '<averageMonthRent>' . $totalMonth / $noOfProperties . '</averageMonthRent>';
    echo '</station>';
}
echo '</stations>';