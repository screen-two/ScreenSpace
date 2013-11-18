<?php 

//connect to the db
require_once('db-connect.php');

for ($i=0; $i<=6; $i++){
	
	// set the variable url to our script that prints out the sql we need. 
	$url = 'http://digitalinc.ie/ScreenSpace/tv-schedule-to-sql.html?offset='.$i;
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
		
		//$result = mysql_query($data) or die(mysql_error());
		
		//(<div id="html">.*<\/div>)
		print_r($data);
		preg_match('/(<div id="html">.*<\/div>)/', $data, $sql);
		
		print_r($sql);
		
		//print_r($data);
		curl_close($ch);
	}
}

?>

