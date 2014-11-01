<?php
/**
 * Created by PhpStorm.
 * User: nafSadh
 * Date: 01-Nov-14
 * Time: 6:12 AM
 */
#echo ($_GET['url']);
$url = ($_GET['url']);
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
echo $response;
// Close the cURL session
curl_close($session);
?>