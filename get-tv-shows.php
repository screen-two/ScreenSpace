<?

//set the response header to json
//changed the header to included charset UTF8 so that the Irish language TV shows on TG4 are understood properly
header('Content-type: application/json; charset=UTF-8');

//connect to the database
require_once('db-connect.php');
/*
 * Following code was adapted from the GrangeMobile App from Semester one. 
 */
//Needs to be in the format mm-dd-yyyy hh:mm:ss
$tv_start_time="";
$tv_end_time="";

//grab TV show start time
//strtotime from http://www.php.net/manual/en/function.strtotime.php
//date - php function parsing strtotime into mySQL format (date). http://php.net/manual/en/function.date.php
if( isset($_GET) && isset($_GET['tv_start_time']) ){
	//forced the start time to be at the beginning of the day
	$tv_start_time = date("Y-m-d 00:00:00", strtotime($_GET['tv_start_time']));
} 

//grab TV show end time
if( isset($_GET) && isset($_GET['tv_end_time']) ){
	$tv_end_time = date("Y-m-d H:i:s", strtotime($_GET['tv_end_time']));
}

// array for JSON response
$response = array();

$sql = "SELECT * FROM TV_Show ORDER BY name";
//if we have a start time and an end time, use the following SQL. 
if ($tv_start_time > 0 && $tv_end_time > 0 ){
	//I have two columns called 'name 'in both TV_Station and TV_Show, here I've aliased the 'name' in station as 'station'
	//Give me all of the TV shows that have a start time between the given start and end times. Ensure that the most recent start_time is being displayed, since that is what will be showing on TV at that time.  
	//Calculate the MAX start_time between two given times for each TV station then select out the TV shows associated with that time and station. 
	$sql = sprintf("
SELECT 
	tv.*, ss.start_time AS start_time, ss.stop_time, s.name AS station, 
	(SELECT count FROM Saved_Search_History AS ssh WHERE ssh.saved_search_id = tv.saved_search_id ORDER BY timestamp DESC Limit 1
	) AS count 
FROM TV_Show AS tv 
INNER JOIN TV_Show_Schedule AS ss
	ON ss.tv_show_id = tv.tv_show_id
INNER JOIN (
	SELECT 
		MAX( ss.start_time ) AS start_time,
		ss.tv_station_id
	FROM TV_Show_Schedule AS ss 
	WHERE ( ss.start_time BETWEEN '%s' AND '%s' ) OR ( ss.start_time < '%s' AND ss.stop_time > '%s' )
	GROUP BY ss.tv_station_id
	ORDER BY ss.tv_station_id, ss.start_time DESC
) AS on_now
	ON on_now.tv_station_id = ss.tv_station_id
		AND on_now.start_time = ss.start_time
INNER JOIN TV_Station AS s 
	ON s.tv_station_id = on_now.tv_station_id
;
", $tv_start_time, $tv_end_time, $tv_start_time, $tv_end_time);
}

// execute the sql
$result = mysql_query($sql) or die(mysql_error());
// check for empty result
if (mysql_num_rows($result) > 0)
 {
    // looping through all results
    $response["tv_shows"] = array();
    while ($row = mysql_fetch_array($result)) 
     {
        // temp show array
        $show = array();
		$show["tv_show_id"] = $row["tv_show_id"];
        $show["saved_search_id"] = $row["saved_search_id"];
        $show["name"] = $row["name"];
		$show["hashtag"] = $row["hashtag"];
		$show["count"] = $row["count"];
        
		if ($tv_start_time > 0 && $tv_end_time > 0 ){ 
			$show["station"] = $row["station"];
			$show["start_time"] = $row["start_time"];
			$show["stop_time"] = $row["stop_time"];
		}
       // push single product into final response array
        array_push($response["tv_shows"], $show);
    }
    // success
    $response["success"] = 1;
	//This is just for testing the sql query
	//$response["sql"] = $sql;
    // echoing JSON response
    print (json_encode($response));

}
else {
    // no TV shows found
    $response["success"] = 0;
    $response["message"] = "Sorry! We didn't find any TV shows.";
	$response["error"] = mysql_error();
    // echo no users JSON
    print (json_encode($response));

}


?>

