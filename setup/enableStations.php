<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
date_default_timezone_set("UTC");
require "../src/aws/aws-autoloader.php";
require '../../cred.php';
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Model\BatchRequest\WriteRequestBatch;
use Aws\DynamoDb\Model\BatchRequest\PutRequest;
use Aws\DynamoDb\Model\Item;
$client = DynamoDbClient::factory(array(
    'key'    => $aws_key,
    'secret' => $aws_secret,
    'region' => 'eu-west-1'
));
$putBatch = WriteRequestBatch::factory($client);
$count = 0;
$dbStationInfo = $client->scan(array('TableName' => 'station_info'));
foreach ($dbStationInfo['Items'] as $item) {
    if(!isset($item['last_updated']['S'])) {
        $response = $client->updateItem(array(
            'TableName' => 'station_info',
            'Key' => array(
                'station_id' => array( 'S' => $item['station_id']['S'] )
            ),
            'ExpressionAttributeValues' =>  array(
                ':val1' => array(
                    'S' => '1')
            ) ,
            'UpdateExpression' => 'set last_updated = :val1'
        ));
        var_dump($response);
        echo '<br><br>';
        $count++;
        if($count > 10)
            break;
    }

}