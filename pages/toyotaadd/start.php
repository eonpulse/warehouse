<?php
// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] == "POST" ) {
    mb_internal_encoding("UTF-8");
    $mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }	
	
    
    $query = "INSERT INTO toyota (product, lot, box, quantity, user) VALUES (?,?,?,?,?)";
            $statement = $mysqli->prepare($query);
            //s = string, i = integer, d = double,  b = blob
            $statement->bind_param('iiids', $_POST['product'], $_POST['lot'], $_POST['box'], $_POST['quantity'], $_POST['user']);
            if($statement->execute()){
                 $clientid = $statement->insert_id;
            }else{
            die('MESSAGE Error : ('. $mysqli->errno .') '. $mysqli->error);
            }
            $statement->close();
    

//$file = 'files/test.txt';
//file_put_contents($file, $_POST['product']."_".$_POST['user']."\n", FILE_APPEND | LOCK_EX);




} else {
//$file = 'files/test.txt';
//file_put_contents($file, '00000', FILE_APPEND | LOCK_EX);
    mb_internal_encoding("UTF-8");
    $mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }	
	
    $product = $this->url[2];
    $lot = $this->url[3];
    $box = $this->url[4];
    $quantity = $this->url[5];
    $user = $this->url[6];
    
    
    
    $query = "INSERT INTO toyota (product, lot, box, quantity, user) VALUES (?,?,?,?,?)";
            $statement = $mysqli->prepare($query);
            //s = string, i = integer, d = double,  b = blob
            $statement->bind_param('iiids', $product, $lot, $box, $quantity, $user);
            if($statement->execute()){
                 $clientid = $statement->insert_id;
            }else{
            die('MESSAGE Error : ('. $mysqli->errno .') '. $mysqli->error);
            }
            $statement->close();



}





?>



    
}
