<?php
/**
 * Created by PhpStorm.
 * User: nafSadh
 * Date: 10-Nov-14
 * Time: 12:22 AM
 */
//echo 'artist_name'.($_GET['artist_name']);
require_once 'EventfulApi.php';
// Define your Eventful API key (Eventful must provide you with a key)
$apiKey = '2Rb92K66KTv7gCvM';
// Create an instance
$eventfulApi = new EventfulApi($apiKey);

$latt = $_GET['latt'];
$long = $_GET['long'];
$loc = $latt.', '.$long;
$date = $_GET['d'];
$next_date = $_GET['nd'];
$eventDate = $date.'00-'.$next_date.'00';
		$args = array(
        'location' => $loc,
        'date' => $eventDate,
        'within' => '10',
        'page_size' => '7',
        'sort_order' => 'popularity'
    );
		$isSuccessful = $eventfulApi->call('events/search', $args);
		if ($isSuccessful)		{
      echo "<small>nearby events:</small><br/>";
      $eventfulApi->getResponseAsArray();
    }
?>