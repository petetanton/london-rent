<?php
class GetZooplaData {
  var $zooplaKey = "b9bhtu9wt2dtyaec4scvzmar";
  var $propertyListingURL = "http://api.zoopla.co.uk/api/v1/property_listings.xml";
  var $pageSize = 100;

  function getData($postCode, $radius, $listingType, $pageNumber, $noBedrooms) {
    if(!isset($postCode)){ throw new Exception("Please set post code");}
    if(!isset($radius)){ throw new Exception("Please set radius");}
    if(!isset($listingType)){ throw new Exception("Please set listing type");}
    if($listingType != 'rent' && $listingType != 'sale'){ throw new Exception("Invalid listing type");}
    $url = $this->propertyListingURL . "?api_key=" . $this->zooplaKey . "&postcode=" . $postCode . "&radius=" . $radius . "&listing_status=" . $listingType . "&page_size=" . $this->pageSize . "&page_number=" . $pageNumber . "&maximum_beds=" . $noBedrooms . "&minimum_beds=" . $noBedrooms;
    //return $url;
    // $ch = curl_init();
    // curl_setopt( $ch, CURLOPT_URL, $url );
    // $content = curl_exec( $ch );
    // $response = curl_getinfo( $ch );
    // curl_close ( $ch );
        error_log($url);
    return shell_exec('curl "' . $url . '"');
  }
}
?>
