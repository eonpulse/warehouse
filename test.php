<?php
echo "Test";

function getsql() {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli('10.20.2.21', 'sa', 'infocom-ltd123', 'Infocom.Barcode');
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
    $querystring = "SELECT * FROM Status LIMIT 10";
    $results = $mysqli->query($querystring);
    while($row = $results->fetch_assoc()) {
        echo $row['Name']."<br>";
    }
    
    /*
    $query = "INSERT INTO manual (date, action, operator, product, lot, box, quantity1, quantity2, warehouse1, warehouse2, cause) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $statement = $mysqli->prepare($query);
    //s = string, i = integer, d = double,  b = blob
    $statement->bind_param('iisiiiddsss', $date, $action, $operator, $product, $lot, $box, $quantity1, $quantity2, $warehouse1, $warehouse2, $cause);
    if($statement->execute()){
        //print 'Success! ID of last inserted record is : ' .$statement->insert_id .'<br />';
    }else{
        echo $str."<br>";
        die('MESSAGE Error : ('. $mysqli->errno .') '. $mysqli->error);
    }
    $statement->close();
    */
}
echo "Test";
?>