<?php
/**
 * Created by PhpStorm.
 * User: nafSadh and amitav
 * Date: 01-Nov-14
 * Time: 6:12 AM
 */
//echo 'artist_name'.($_GET['artist_name']);
require_once 'EventfulApi.php';

// Define your Eventful API key (Eventful must provide you with a key)
$apiKey = '2Rb92K66KTv7gCvM';

// Create an instance
$eventfulApi = new EventfulApi($apiKey);

$artist_name = ($_GET['artist_name']);
$url="http://api.bandsintown.com/artists/".$artist_name."/events.json?app_id=tail2tune";
// Initialize the cURL session with the request URL
$session = curl_init($url);

// Tell cURL to return the request data
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

// Set the HTTP request authentication headers
$headers = array(
);
curl_setopt($session, CURLOPT_HTTPHEADER, $headers);

// Execute cURL on the session handle
$response = curl_exec($session);

$json = json_decode($response, true);

//echo $response;
curl_close($session);
foreach ($json as $item)
{
	$dates  = explode("T", $item['datetime']);
	$date = str_replace("-","",$dates[0]);
	$next_date = date('Ymd', strtotime($dates[0] .' +1 day'));
	$lon = $item['venue']['longitude'];
	$lat = $item['venue']['latitude'];
?>	
	<div class="event">
		<span class="city"><?php echo $item['venue']['city'];?></span>
		<span class="venue"><a href="<?php echo $item['venue']['url'];?>"><?php echo $item['venue']['name'];?></a></span>
		<span class="date"><?php echo date('jS F, Y', strtotime($dates[0]));?></span>
		<hr />
    <div class='placeDetail'>
		<span class="region"><?php echo $item['venue']['region'];?></span>
		<span class="country"><?php echo $item['venue']['country'];?></span>
    </div>
		<div class="hotels">
		<?php 
		$url="http://www.priceline.com/api/hotelretail/listing/v3/".$lat.",".$lon."/".$date."/".$next_date."/1/50?offset=0&sort=1";
		// Initialize the cURL session with the request URL
		$session = curl_init($url);

		// Tell cURL to return the request data
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

		// Set the HTTP request authentication headers
		$headers = array(
		);
		curl_setopt($session, CURLOPT_HTTPHEADER, $headers);

		// Execute cURL on the session handle
		$response = curl_exec($session);

		$json = json_decode($response, true);
		curl_close($session);
		
		$i = 0;
		foreach ($json['hotels'] as $item)
		{
			$i++;
		?>
			<div class="hotelDiv">
				<span class="hotelname"><a href="<?php echo 'http://www.priceline.com/hotel/hotelOverviewGuide.do?propID='.$item['pclnHotelID'];?>"><?php echo $item['hotelName'];?></a></span>
				<img src="<?php echo $item['thumbnailURL'];?>" height="128pt" />
				<span class="price"><?php echo $item['currencyCode']." ".$item['merchPrice'];?></span>
				<span class="rating"><?php echo round(floatval($item['overallRatingScore']), 2);?><span class="outof">/10</span></span>
			</div>
		<?php 
			if($i == 3)
				break;
		} 
		?>
		</div>
    <div class='clrflt'></div>
		<div class="nearEvents">
      nearby events: 
		<?php
		$loc = $lat.', '.$lon;
		$eventDate = $date.'00-'.$next_date.'00';
		// Attempt to search for events in Los Angeles, CA
		$args = array(
			'location' => $loc,
			'date' => $eventDate,
			'within' => '10'
		);
		$isSuccessful = $eventfulApi->call('events/search', $args);
		if ($isSuccessful)
		{
			// Output the response as a string
			//echo $eventfulApi->getResponseAsString();
   
			// Output the response as an array
			$eventfulApi->getResponseAsArray();
		}
		?>
		</div>
</div>
<?php	
	
	//echo $response;
}

// Close the cURL session

?>