<?

//set the response header to json
//changed the header to included charset UTF8 so that the Irish language TV shows on TG4 are understood properly
header('Content-type: application/json; charset=UTF-8');

//connect to the database
require_once('db-connect.php');


// array for JSON response
$response = array();

$sql = "SELECT name FROM TV_Station";


// execute the sql
$result = mysql_query($sql) or die(mysql_error());
// check for empty result
if (mysql_num_rows($result) > 0)
 {
    // looping through all results
    $response["tv_stations"] = array();
    while ($row = mysql_fetch_array($result)) 
     {
        // temp show array
        $show = array();
        $show["name"] = $row["name"];
        // push single product into final response array
        array_push($response["tv_stations"], $show);
    }
    // success
    $response["success"] = 1;
	//This is just for testing the sql query
	//$response["sql"] = $sql;
    // echoing JSON response
    print (json_encode($response));

}
else {
    // no products found
    $response["success"] = 0;
    $response["message"] = "Sorry! We didn't find any TV stations.";
	$response["error"] = mysql_error();
    // echo no users JSON
    print (json_encode($response));

}


?>

