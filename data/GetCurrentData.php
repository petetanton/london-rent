<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
date_default_timezone_set("UTC");
require "../src/aws/aws-autoloader.php";
require "../../cred.php";
use Aws\DynamoDb\DynamoDbClient;
$client = DynamoDbClient::factory(array(
    'key'    => $aws_key,
    'secret' => $aws_secret,
    'region' => 'eu-west-1'
));
try {
    $dbStationInfo = $client->scan(array('TableName' => 'station_info'));
} catch (\Aws\DynamoDb\Exception\DynamoDbException $e) {
    header("Access-Control-Allow-Origin: *");
    header("ContentType: xml/text");
    die("<error><title>DynamoDB error</title><info>" . $e->getAwsErrorType() . "</info><code>" . $e->getAwsErrorCode() . "</code></error>");
}
try {
    $dbPropertyListings = $client->scan(array('TableName' => 'property_listings'));
} catch (\Aws\DynamoDb\Exception\DynamoDbException $e) {
    die("<error><title>DynamoDB error</title><info>" . $e->getAwsErrorType() . "</info><code>" . $e->getAwsErrorCode() . "</code></error>");

}


foreach ($dbStationInfo['Items'] as $item) {
    if(strpos($item['london_zone']['S'],'1' ) === 0 && isset($item['last_updated']['S'])) {
        $stations_stationName[] = $item['station_name']['S'];
        $stations_postCode[] = $item['post_code']['S'];
        $stations_londonZone[] = $item['london_zone']['S'];
        $stations_stationId[] = $item['station_id']['S'];
        $stations_stationLat[] = $item['latitude']['S'];
        $stations_stationLong[] = $item['longitude']['S'];
        if (isset($item['last_updated'])) {
            $stations_lastUpdate[] = $item['last_updated']['S'];
        } else {
            $stations_lastUpdate[] = 0;
        }
    }
}
if(!isset($stations_postCode)) { header("ContentType: xml/text"); die("<error><info>404: No data found</info></error>");}
header("Content-type: text/xml");
header("Access-Control-Allow-Origin: *");

echo '<stations cache-time="' . date("Y-m-d H:i:s", time()) . '"    >';
foreach ($dbPropertyListings['Items'] as $item) {
    $listing_listingId[] = $item['listing_id']['S'];
    $listing_noBeds[] = $item['no_bed']['S'];
    $listing_rentMonth[] = $item['price_rent_month']['S'];
    $listing_stationId[] = $item['station_id']['N'];
}
for($i=0;$i<count($stations_postCode);$i++) {
    $noOfProperties = 0;
    $totalMonth = 0;
    $stations_listingRent = array();
    for($j=0; $j<count($listing_listingId); $j++) {
        if($listing_stationId[$j] == $stations_stationId[$i]){
            $noOfProperties++;
            $stations_listingRent[] = $listing_rentMonth[$j];
            $totalMonth = $totalMonth + $listing_rentMonth[$i];
        }
    }

    if($noOfProperties == 0) {
        $averagePrice = 0;
    } else {
        $averagePrice = $totalMonth / $noOfProperties;
        $maxPrice = max($stations_listingRent);
        $minPrice = min($stations_listingRent);
        echo '<station>';
            echo '<stationId>' . $stations_stationId[$i] . '</stationId>';
            echo '<stationName>' . $stations_stationName[$i] . '</stationName>';
            echo '<stationLondonZone>' . $stations_londonZone[$i] . '</stationLondonZone>';
            echo '<location>';
                echo '<stationPostCode>' . $stations_postCode[$i] . '</stationPostCode>';
                echo '<geo>';
                    echo '<latitude>' . $stations_stationLat[$i] . '</latitude>';
                    echo '<longitude>' . $stations_stationLong[$i] . '</longitude>';
                echo '</geo>';
            echo '</location>';
            echo '<noOfListings>' . $noOfProperties . '</noOfListings>';
            echo '<price>';
                echo '<averageMonthRent>' . $averagePrice . '</averageMonthRent>';
                echo '<minMonthRent>' . $minPrice . '</minMonthRent>';
                echo '<maxMonthRent>' . $maxPrice . '</maxMonthRent>';
            echo '</price>';
            echo '<lastUpdated>' . date("Y-m-d H:i:s", $stations_lastUpdate[$i]) . '</lastUpdated>';
        echo '</station>';
    }

}
echo '</stations>';