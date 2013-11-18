<?php 

//set the response header to json
//changed the header to included charset UTF8 so that the Irish language TV shows on TG4 are understood properly
header('Content-type: html/text; charset=UTF-8');

require_once('display_lib.php');
session_start();
require_once("twitteroauth/twitteroauth.php"); //Path to twitteroauth library, code from The Web Dev Door by Tom Elliott (http://www.webdevdoor.com/php/authenticating-twitter-feed-timeline-oauth/)



$twitteruser = "thisdigitalinc";
$consumerkey = "oXRHpijPXqkmpI01vB3XKQ";
$consumersecret = "EBxsXvSZaDeiN08kHHtWaiiZyiGOdpsIP0UGBwy2g";
$accesstoken = "1615587769-mhzmHR2bWQVlueppiW1mjwkrGMdGdw4qmoC6IMU";
$accesstokensecret = "AcKfb0CjRi3mun0dpFQAhubh4Br8hLmlwac8G4IDJE";
 
function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
  $connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
  return $connection;
}
 
$connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);


// The search terms are passed in the q parameter
// search_server.php?q=[search terms]
if (!empty($_GET['q'])) {
	
	$last_tweet_id = '';
		if(empty($result)){  
			//insert search
			$query = mysql_query(sprintf("INSERT INTO Saved_Search ( hashtag ) VALUES ('%s') WHERE NOT EXISTS ( SELECT saved_search_id FROM Saved_Search WHERE hashtag = '%s' LIMIT 1 )", mysql_real_escape_string($q), mysql_real_escape_string($q)));  
			$query = mysql_query("SELECT * FROM Saved_Search WHERE saved_search_id = " . mysql_insert_id());  
			$result = mysql_fetch_array($query);  
			$search_id = $result['saved_search_id'];
			$last_tweet_id = $result['last_tweet_id'];
		} else {  
			$search_id = $result['saved_search_id'];
			$last_tweet_id = $result['last_tweet_id'];
	}
		
	// Remove any hack attempts from input data
	$search_terms = $_GET['q'];
	
	if(strpos($search_terms, '#') === 0){
		$search_terms = substr($search_terms, 1);
	}	
	
	
	$count=100;
	//get until from querystring
	if( isset($_GET) && isset($_GET['count']) ){
		$count=$_GET['count'];
	}

	$tweets = $connection->get("https://api.twitter.com/1.1/search/tweets.json?q=".$search_terms."&count=".$count."&include_entities=0");
	
	

	$tweet_data = $tweets->{'statuses'};

	// Load the template for tweet display
	$tweet_template= file_get_contents('tweet_template.html');
	
	$counts = array();
	
	// Create a stream of formatted tweets as HTML
	$tweet_stream = '';
	foreach($tweet_data as $tweet) {
		
		// Ignore any retweets
		if (isset($tweet->{'retweeted_status'})) {
			continue;
		}
		
		// Get a fresh copy of the tweet template
		$tweet_html = $tweet_template;
		
		// Insert this tweet into the html
		// I have changed the syntax here because we're using class objects rather than arrays.  
		$tweet_html = str_replace('[screen_name]',
			$tweet->{'user'}->{'screen_name'},$tweet_html);
		$tweet_html = str_replace('[name]',
			$tweet->{'user'}->{'name'},$tweet_html);		
		$tweet_html = str_replace('[profile_image_url]',
			$tweet->{'user'}->{'profile_image_url'},$tweet_html);
		$tweet_html = str_replace('[tweet_id]',
			$tweet->{'id'},$tweet_html);
		$tweet_html = str_replace('[tweet_text]',
			linkify($tweet->{'text'}),$tweet_html);
		$tweet_html = str_replace('[created_at]',
			twitter_time($tweet->{'created_at'}),$tweet_html);
		$tweet_html = str_replace('[retweet_count]',
			$tweet->{'retweet_count'},$tweet_html);			
		
		// Add the HTML for this tweet to the stream
		$tweet_stream .= $tweet_html;
		
	}
		
	// Pass the tweets HTML back to the Ajax request
	print $tweet_stream;

} else {
	print 'No search terms found';
}	

?>