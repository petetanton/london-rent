<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
date_default_timezone_set("Europe/London");
require "src/aws/aws-autoloader.php";
require "../cred.php";
use Aws\DynamoDb\DynamoDbClient;
$zooplaKey = getenv("ZOOPLA_KEY");
$client = DynamoDbClient::factory(array(
 'key'    => $aws_key,
 'secret' => $aws_secret,
 'region' => 'eu-west-1'
));
$response = $client->scan(array(
    'TableName' => 'station_info'
	)
);

//     'IndexName' => 'london_zone-index',
//     'KeyConditionExpression' => '#dt = :v_dt',
//     'ExpressionAttributeNames' => array ('#dt' => 'london_zone'),
//     'ExpressionAttributeValues' =>  array (':v_dt' => array('S' => '1')),
//     'Select' => 'ALL_ATTRIBUTES',
//     'ScanIndexForward' => true,
//     )
// );
foreach ($response['Items'] as $item) {
  // die($item['london_zone']['S']);
  if(strpos($item['london_zone']['S'],'1' ) === 0 && isset($item['last_updated'])) {
    echo "Station ---> " . $item['station_name']['S'] . "</br>";
    echo "Post Code ---> " . $item['post_code']['S'] . "</br>";
    echo "London Zone ---> " . $item['london_zone']['S'] . "</br>";
    if(isset($item['last_updated'])) {
      echo "Last Updated ---> " . date("Y-m-d H:i:s", $item['last_updated']['S']) . "</br>";
    }
    echo "London Zone Search ---> " . strpos($item['london_zone']['S'],'1') . "<br>";
    if (strpos($item['london_zone']['S'],'1') === false) {
      echo 'false';
    } else {
      echo 'true';
      
    }
    echo "</br>";
  }

}
?>
