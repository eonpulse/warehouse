<?php
include('ini.php');
mb_internal_encoding("UTF-8");
$mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
$product = $_POST['product'];
$lot = $_POST['lot'];
$box = $_POST['box'];
$note = $_POST['note'];
if($product != 0 && $lot != 0 && $box !=0) { 
    $query = "DELETE FROM alerts WHERE product=? AND lot=? AND box=?";
    $statement = $mysqli->prepare($query);
    $statement->bind_param('iii', $product, $lot, $box);
} else { 
    $query = "DELETE FROM alerts WHERE note=?"; 
    $statement = $mysqli->prepare($query);
    $statement->bind_param('s', $note);
}
//echo $query;

if($statement->execute()){
    print 'Коробка успешно удалена';
}else{
    echo $str."<br>";
    die('ALARM UPDATE Error : ('. $mysqli->errno .') '. $mysqli->error);
}
$statement->close();

/*
$query = "DELETE FROM alerts WHERE status=0";
$statement = $mysqli->prepare($query);
$statement->bind_param('iii', $product, $lot, $box);
if($statement->execute()){
    print 'Коробка успешно удалена';
}else{
    echo $str."<br>";
    die('ALARM UPDATE Error : ('. $mysqli->errno .') '. $mysqli->error);
}
$statement->close();
*/


?>