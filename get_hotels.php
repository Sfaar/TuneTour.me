<div class="bigMapHolder">
  <div id="bigmapShow" onclick="loadBigMap()">click here for tour map</div>
  <div id="bigmapcanvas" ></div>
</div>
<?php
set_time_limit(0);
$getHotel = 0;
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
$bigMapData = array();
$bmi = 1;
foreach ($json as $item)
{
	$dates  = explode("T", $item['datetime']);
	$date = str_replace("-","",$dates[0]);
	$next_date = date('Ymd', strtotime($dates[0] .' +1 day'));
	$lon = $item['venue']['longitude'];
	$lat = $item['venue']['latitude'];
  $cityName = $item['venue']['city'];
  $bigMapData[] = [$cityName, $lat, $lon, $bmi];
?>	
	<div class="event">
		<span class="city"><?php echo $cityName; ?></span>
		<span class="venue"><a href="<?php echo $item['venue']['url'];?>"><?php echo $item['venue']['name'];?></a></span>
		<span class="date"><?php echo date('jS F, Y', strtotime($dates[0]));?></span>
		<hr />
    <div class='placeDetail'>
		<span class="region"><?php echo $item['venue']['region'];?></span>
		<span class="country"><?php echo $item['venue']['country'];?></span>
    </div>
    <?php if($getHotel==true){ ?>
		<div class="hotels">
      <span class="hotelsSectionTag">stay around:</span><br />
		<?php 
		$url="http://www.priceline.com/api/hotelretail/listing/v3/".$lat.",".$lon."/".$date."/".$next_date."/1/5?offset=0&sort=1";
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

    $venueMapData = array();
		$i = 0;
		foreach ($json['hotels'] as $item)
		{
			$i++;
      $info = '<div><img src=%2q%'.$item['thumbnailURL'].'%2q%><br><b>'.$item['hotelName'].'</b><br>'.round(floatval($item['overallRatingScore']), 2).'/10 | '.$item['currencyCode'].' '.round($item['merchPrice'],0).'</div>';
      $info = htmlspecialchars($info);

      $venueMapData[] = [$item['hotelName'], $item['lat'], $item['lon'], $i, $info];
		?>
			<div class="hotelDiv">
				<div class="imgcont"><img src="<?php echo $item['thumbnailURL'];?>" width="48px" /></div>
        <div>
          <span class="hotelname"><a href="<?php echo 'http://www.priceline.com/hotel/hotelOverviewGuide.do?propID='.$item['pclnHotelID'];?>"><?php echo $item['hotelName'];?></a></span>
          <br/>
          <span class="price"><?php echo $item['currencyCode']." ".round($item['merchPrice'],0);?><small>/night</small></span>
				  <span class="rating"> (<small>rating:</small><?php echo round(floatval($item['overallRatingScore']), 2);?><span class="outof">/10</span>)</span>
        </div>
			</div>
		<?php 
			if($i == 5)
				break;
		} 
		?>
		</div>
    <div class='clrflt'></div>
    <div class="hotelsMapContainer">
      <div class="hotelmapmeta" id="hmc<?php echo $bmi;?>meta" onclick="loadHotelMap('hmc<?php echo $bmi;?>')">
        click here for map of nearby hotels
        <span class="hotelMapData" id="hmc<?php echo $bmi;?>dt" style="display: none"><?php echo json_encode($venueMapData); ?></span>
      </div>
      <div class="hotelmapcanvas" id="hmc<?php echo $bmi;?>" >
      </div>
    </div>

    <?php } //end if($getHotel==true) ?>
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
		if ($isSuccessful)		{
      $eventfulApi->getResponseAsArray();
		}
		?>
		</div>
</div>
<?php
  $bmi++;
}
// Close the cURL session

?>
<script type="application/javascript">
  function initBigMap(){
    var map = new google.maps.Map(document.getElementById('bigMapCanvas'));
    var bmMarkers = <?php echo json_encode($bigMapData); ?>
    setMarkers(map, bmMarkers);
  }
</script>

<span id="bigMapData" style="display: none;"><?php echo json_encode($bigMapData); ?></span>