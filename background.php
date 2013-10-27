<?php

//We have added background.php, this checks for the current trends and adds them to the DB, 
//once this is done, it then loops though all of the saved searches and calculates their counts. 
//Trends are now stored as saved searches (gets around loading times)

set_time_limit(60);
session_start();
require_once("twitteroauth/twitteroauth.php"); //Path to twitteroauth library, code from The Web Dev Door by Tom Elliott (http://www.webdevdoor.com/php/authenticating-twitter-feed-timeline-oauth/)

//connect to the database
require_once('db-connect.php');
	
$twitteruser = "thisdigitalinc";
$notweets = 30;

$consumerkey = "orPxnRuTilC0W9e8NPt4AA";
$consumersecret = "NCfX7K4JdMPJtQ5PypfAt281Fm3mboIYTtO1PQBVdI";
$accesstoken = "1615587769-OMh8nVTgIRDweHq7M2Cwkkm6Rik0yFmni9x0sSk";
$accesstokensecret = "o8a26KW7kGTocJF71loU3UE4Hh5yegPaceN9X0ngtI";
 
function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
  $connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
  return $connection;
}
 
$connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);

$latitude = "53.339381";
$longitude = "-6.260533";
$radius = "1000km";


$limits = $connection->get("https://api.twitter.com/1.1/application/rate_limit_status.json?resources=trends,search");

$search_limit = $limits->{'resources'}->{'search'}->{'/search/tweets'}->{'remaining'};
$trend_limit = $limits->{'resources'}->{'trends'}->{'/trends/place'}->{'remaining'};
echo "search limit: " . $search_limit . "<br/>";
echo "trend limit: " . $trend_limit . "<br/>";

//Do we have this search already saved?
//http://php.net/manual/en/function.mysql-query.php
$base_query = mysql_query("SELECT * FROM Saved_Search");  

while ($row = mysql_fetch_assoc($base_query)) {	
	$q = $row['hashtag'];
	$search_id = $row['saved_search_id'];
	$last_tweet_id = $row['last_tweet_id'];
	set_time_limit(60);
	echo "Starting: $q" . "<br/>";
	
	$limits = $connection->get("https://api.twitter.com/1.1/application/rate_limit_status.json?resources=trends,search");

	$search_limit = $limits->{'resources'}->{'search'}->{'/search/tweets'}->{'remaining'};
	$trend_limit = $limits->{'resources'}->{'trends'}->{'/trends/place'}->{'remaining'};
	echo "search limit: " . $search_limit . "<br/>";
	echo "trend limit: " . $trend_limit . "<br/>";
	
	if(intval($search_limit) < 1) {
		echo "rate limit hit<br/>";
		end;
	}
	
	$status = array();
	
	if(isset($last_tweet_id) && !empty($last_tweet_id)) {
		$tweets = $connection->get("https://api.twitter.com/1.1/search/tweets.json?count=100&include_entities=0&since_id=" . $last_tweet_id . "&geocode=" . $latitude . "," . $longitude . "," . $radius . "&q=".$q);
	}
	else {
		$tweets = $connection->get("https://api.twitter.com/1.1/search/tweets.json?count=100&include_entities=0&geocode=" . $latitude . "," . $longitude . "," . $radius . "&q=".$q);
	}
	
	$status = array_merge($status, $tweets->{'statuses'});
	
	$meta = $tweets->{'search_metadata'};
	//$next is sent from the twitter api and details the next querystring to use. 
	$next = $meta->{'next_results'};
	$max = 20;
	$last_tweet_id = $meta->{'max_id_str'};
	$refresh_url = $meta->{'refresh_url'};
	
	while(isset($next) && $max > 0){
		
		echo "Max pages left: $max" . "<br/>";
		$new_tweets = $connection->get("https://api.twitter.com/1.1/search/tweets.json".$next);
		$status = array_merge($status, $new_tweets->{'statuses'});
		
		$meta = $new_tweets->{'search_metadata'};
		
		if(isset($meta)) {
			$next = $meta->{'next_results'};
		}
		else
		{
			unset($next);
		}
		
		$max -= 1;
	}
	
	$counts = array();
	
	foreach ($status as $tweet){
		$date = date_parse( $tweet->{"created_at"} );
		$key = $date['year'] . '-' . $date['month'] . '-' . $date['day'] . ' ' . $date['hour'] . ':00:00';
		
		if(!array_key_exists($key, $counts))
			$counts[$key] = 0;
			
		$counts[$key] = $counts[$key] + 1;
	}	
	
	//update the last tweet id and refresh_url for this search
	$query = mysql_query(sprintf("UPDATE Saved_Search SET last_tweet_id  = '%s', timestamp = NOW() WHERE saved_search_id = %d", $last_tweet_id, $search_id));
	
	
	//Save all of the counts (or update as needed)
	foreach ($counts as $date => $count)
	{
		$sql = sprintf("SELECT * FROM Saved_Search_History WHERE saved_search_id = '%d' AND timestamp = '%s'", $search_id, $date);
		$query = mysql_query($sql);  
		$result = mysql_fetch_array($query);
		
		if(empty($result)){ 
			$sql = sprintf("INSERT INTO Saved_Search_History ( saved_search_id, timestamp, count ) VALUES ('%d', '%s', '%d')", $search_id, $date, $count);
			$query = mysql_query($sql); 
			
		} else {
			$history_id = $result['saved_search_history_id'];
			
			//Add counts together, because we used last tweet id
			$query = mysql_query(sprintf("UPDATE Saved_Search_History SET count = count + %d WHERE saved_search_history_id = '%d'", $count, $history_id));
		}
	}
}

echo "completed";
?>



