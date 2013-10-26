<?

//set the response header to json
header('Content-type: application/json');

//connect to the database
require_once('db-connect.php');
/*
 * Following code was adapted from the GrangeMobile App from Semester one. 
 */

// array for JSON response
$response = array();


// get all courses from course table
$result = mysql_query("SELECT * FROM TV_Show ORDER BY name") or die(mysql_error());
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
        

       // push single product into final response array
        array_push($response["tv_shows"], $show);
    }
    // success
    $response["success"] = 1;
    // echoing JSON response
    print (json_encode($response));

}
else {
    // no products found
    $response["success"] = 0;
    $response["message"] = "Sorry! We didn't find any TV shows.";

    // echo no users JSON
    print (json_encode($response));

}


?>

