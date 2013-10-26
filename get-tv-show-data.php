<?php 
//set the response header to json
header('Content-type: application/json');

session_start();

//connect to the database
require_once('db-connect.php');

//retrieve querystring parameters

$tv_show_id="";

//grab TV show, if not there die
if( isset($_GET) && isset($_GET['tv_show_id']) ){
	$tv_show_id = $_GET['tv_show_id'];
} else {
	die('no params provided');
}


//Grab the all the results for the specified TV show
$sql = sprintf("SELECT * FROM Saved_Search AS saved
INNER JOIN Saved_Search_History AS history
	ON history.saved_search_id = saved.saved_search_id
WHERE saved.tv_show_id = %d ORDER BY saved.tv_show_id, history.timestamp ASC", $tv_show_id);

$query = mysql_query($sql);  

//create array of arrays for result sets and data points
//Declan I'm leaving this here for you, I don't know what you need yet. 
$results = array();
$positions = array();
$pos = 0;

//read results from sql query
while ($row = mysql_fetch_assoc($query)) {	
	$term = $row['hashtag'];
	
	$dataset = array();
	
	//keep track of search term position in results array
	if(array_key_exists($term, $positions)){
		$pos = $positions[$term];
	} else {
		$positions[$term] = count($positions);
		$pos = $positions[$term];
	}
	
	//initialize the dataset array
	if(array_key_exists($pos, $results)){
		$dataset = $results[$pos];
	} else {
		$dataset = array( name => $term, data => array() );
		$results[$pos] = $dataset;
	}
	
	
	//grab history values
	$epoch = strtotime( $row['timestamp'] );
	$count = intval($row['count']);
	
	//store datapoint values in data array
	$entry = array( 'timestamp' => $epoch, 'count' => $count);
	$data = $dataset['data'];
	array_push($data, $entry);
	
	//update parent arrays
	$dataset['data'] = $data;
	$results[$pos] = $dataset;
}

//print out results
print_r( json_encode( $results ) );

?>



