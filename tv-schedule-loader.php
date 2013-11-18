<?php
header('Content-Type: application/json; charset=utf-8');

ini_set('log_errors', 1); 
ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); 
error_reporting(E_ALL);

$day_offset = 1;

// If this is a GET call, and the query string has a value called 'offset', 
// e.g. ?offset=1, then set day_offset to that value. So then we know if 
// we want today (0), tomorrow (1) etc...
if( isset($_GET) && isset($_GET['offset']) ){
	$day_offset = $_GET['offset'];
	// if it is not a query string paramater, default to 1. In nother 
	//words it's assuming you want tomorrows schedule
} else {
	$day_offset = 1;
}

// today at midnight
$today = date("Y-m-d 00:00:00.0"); 

// today plus offset 1 day, 2 days, 3 days and so on...
$today = date('Y-m-d 00:00:00.0', strtotime($today . ' + ' . $day_offset . ' day'));

// set the variable url to the RTE web app url with the selected channel ids, and give me from 'now' plus 24 hours. so from midnight today to midnight tomorrow. 
$url = 'http://rte.vizimo.com/ajax/programmes.htm?z=z2&channels=1611,1612,1692,1678&pkg=ie1A&offset=-1&hours=24&when=' . $today;
// This is cURL, a library that lets you make HTTP requests in PHP http://no.php.net/curl
// Do a GET request pretending to be Firefox, with the same headers visible in Firebug Console. 
$headers = array(
	"GET /HTTP/1.1",
	"User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.1) Gecko/2008070208 Firefox/3.0.1",
	"Content-type: text/xml;charset=\"utf-8\"",
	"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
	"Accept-Language: en-us,en;q=0.5",
	"Accept-Encoding: gzip,deflate",
	"Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7",
	"Keep-Alive: 300",      
	"Connection: keep-alive"
);

// cURL headers & options adapted from http://stackoverflow.com/questions/14102121/how-to-configure-curl-to-send-specific-http-headers-using-php
$ch = curl_init(); 
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 60); 
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// If there's an error display it, if not print out the result http://stackoverflow.com/questions/16134437/php-curl-problems-http-code-0
$data = curl_exec($ch); 
if (curl_errno($ch)) { 
	print "cURL Error: " . curl_error($ch);
}
else {  
	
	print_r($data);
	
	curl_close($ch);
}?>

