<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
date_default_timezone_set("Europe/London");
require "src/aws/aws-autoloader.php";
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Model\BatchRequest\WriteRequestBatch;
use Aws\DynamoDb\Model\BatchRequest\PutRequest;
use Aws\DynamoDb\Model\Item;
include 'src/GetZooplaData.php';
include '../cred.php';
$zoopla = new GetZooplaData();
//header('Content-type: application/xml');
$client = DynamoDbClient::factory(array(
  'key'    => $aws_key,
  'secret' => $aws_secret,
  'region' => 'eu-west-1'
));
$putBatch = WriteRequestBatch::factory($client);
$updateTime = time() - (7*24*3600);

$dbStationInfo = $client->scan(array('TableName' => 'station_info'));
foreach ($dbStationInfo['Items'] as $item) {
  if(strpos($item['london_zone']['S'],'1' ) === 0 && isset($item['last_updated']['S'])) {
    if (intval($item['last_updated']['S']) < $updateTime) {
//      die($item['last_updated']['S'] . " : " . $updateTime);
      $stations_stationName[] = $item['station_name']['S'];
      $stations_postCode[] = $item['post_code']['S'];
      $stations_londonZone[] = $item['london_zone']['S'];
      $stations_stationId[] = $item['station_id']['S'];
      if (isset($item['last_updated'])) {
        $stations_lastUpdate[] = $item['last_updated']['S'];
      } else {
        $stations_lastUpdate[] = 0;
      }
      echo "London Zone Search ---> " . strpos($item['london_zone']['S'], '1') . "<br>";
    }
  }

}
if(!isset($stations_postCode)) { die("Nothing to update");}
//die(var_dump($stations_stationName));
// die(print_r($stations_stationId));
for ($j=0;$j<count($stations_postCode);$j++) {
  $tableScan = $client->getIterator('scan', array('TableName' => 'property_listings'));
  foreach($tableScan as $item) {
    error_log("in for each loop");
    if(intval($item['station_id']['N']) == intval($stations_stationId[$j])) {
      error_log('Deleting: ' . $item['listing_id']['S']);
      $client->deleteItem(array(
        'TableName' => 'property_listings',
          'Key' => array(
            'listing_id' => array('S' => $item['listing_id']['S'])
          )
      ));
    }

  }
  // $xml_raw = $zoopla->getData($stations_postCode[$j],1,"rent",1,1);
  // $listings = simplexml_load_file("tmp.xml");
  // $listings = simplexml_load_string($xml_raw);
  // var_dump($xml);
  // var_dump($xml);
  // echo $listings->listing[1]->description;

  $pageNo = 0;
  $run = true;
  while ($run) {
    $pageNo++;
    $noOfProperties = 0;
      $xml_raw = $zoopla->getData(str_replace(' ', '', $stations_postCode[$j]),0.5,"rent",$pageNo,1);
      // error_log($xml_raw);
      // die($xml_raw);
      $listings = simplexml_load_string($xml_raw);
      foreach ($listings as $info):
        if($info->listing_status == 'rent') {
          $listingId[] = (int)(string)$info->listing_id;
          $noBed[] = (string)$info->num_bedrooms;
          $listingStatus[] = (string)$info->listing_status;
            $priceRentWeek[] = (string)$info->rental_prices->per_week;
            $priceRentMonth[] = (string)$info->rental_prices->per_month;
            $priceSale[] = 0;
            if((string)$info->property_type == "") {
              $propertyType[] = "unknown";
            } else {
              $propertyType[] = (string)$info->property_type;
            }
          $latitude[] = (string)$info->latitude;
          $longitude[] = (string)$info->longitude;
          $firstPublishedDate[] = (string)$info->first_published_date;
          $lastPublishedDate[] = (string)$info->last_published_date;
        }
        $noOfProperties++;
        endforeach;
        if($noOfProperties < 100) {
          $run = false;
          break;
        }
        sleep(2);
        //break;      //temp to only make one api call
      }
    //   var_dump($listingId);
    //   echo '<br><br><br>';
    //   var_dump($noBed);
    //   echo '<br><br><br>';
    //
    //   var_dump($listingStatus);
    //   echo '<br><br><br>';
    //
    //   var_dump(  $priceRentWeek);
    //   echo '<br><br><br>';
    //
    //     var_dump($priceRentMonth);
    //     echo '<br><br><br>';
    //
    //     var_dump($priceSale);
    //     echo '<br><br><br>';
    //
    // var_dump($propertyType);
    // echo '<br><br><br>';
    //
    //   var_dump($latitude);
    //   echo '<br><br><br>';
    //
    //   var_dump($longitude);
    //   echo '<br><br><br>';
    //
    //   var_dump($firstPublishedDate);
    //   echo '<br><br><br>';
    //
    //   var_dump($lastPublishedDate);
    //   die('done');
  $noOfProperties = 0;
  $sumOfRent = 0;
  for($i=0;$i<count($listingId);$i++) {
    echo "Listing ID: " . $listingId[$i] . " (" . $propertyType[$i] . ") rent per month £" . $priceRentMonth[$i] . "</br>";
    $sumOfRent += $priceRentMonth[$i];
    $noOfProperties++;
  }
  echo "<p>Average rent: £" . $sumOfRent / $noOfProperties . " over " . $noOfProperties . " data points (" . $pageNo . " pages)</p>";
  error_log($pageNo + " call(s) made to API");

  $requestNo = 0;
  $noInRequest = 0;
  $itemArray = array();


  $itemIds = array();
  for($i=0; $i<count($listingId); $i++) {
    $itemIds[] = $itemId = uniqid();
    $putBatch->add(new PutRequest(array(
      "listing_id" => array("S" => $itemId),
      "listing_id_zoopla" => array("N" => $listingId[$i]),
      "station_id" => array("N" => $stations_stationId[$j]),
      "no_bed" => array("S" => $noBed[$i]),
      "listing_status" => array("S" => $listingStatus[$i]),
      "price_rent_week" => array("S" => $priceRentWeek[$i]),
      "price_rent_month" => array("S" => $priceRentMonth[$i]),
      "property_type" => array("S" => $propertyType[$i]),
      "latitude" => array("S" => $latitude[$i]),
      "longitude" => array("S" => $longitude[$i]),
      "first_published_date" => array("S" => $firstPublishedDate[$i]),
      "last_published_date"  => array("S" => $lastPublishedDate[$i]),
      "update_time" => array("S" => (string)time())
    ), "property_listings"));
  }
  $putBatch->flush();

//  error_log("Updating Station information");
  $response = $client->updateItem(array(
      'TableName' => 'station_info',
      'Key' => array(
          'station_id' => array( 'S' => $stations_stationId[$j] )
      ),
      'ExpressionAttributeValues' =>  array(
          ':val1' => array(
              'S' => (string)time())
      ) ,
      'UpdateExpression' => 'set last_updated = :val1'
  ));
//  error_log($response);
//  error_log("Station update complete");
 // die(var_dump($itemArray));

// for($i=0;$i<count($itemArray);$i++) {
//   // die(print_r($itemArray[$i]));
//   echo '<br><br><br><p>Start of ' . $i . '</p>';
//   // var_dump($itemArray[$i]);
//   $batchPutResponse = $client->batchWriteItem(
//     array(
//       "RequestItems" => array(
//         "property_listings" => $itemArray[$i]
//       )
//     )
//   );
}

 ?>
