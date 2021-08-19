<?php
$this->inf = '';
mb_internal_encoding("UTF-8");
include('getlogs.php');
$mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
$actions = array("","Перемещение","Поступление","Выбытие","Изменение");

$this->inf .= "<div class='inputform'><form action='' method='post'>";
if(!isset($_POST['showlist'])) {
    //$date1 = date('Y-m-d', time() - 86400);
    $date1 = date('Y-m-d');
    $date2 = date('Y-m-d');
} else {
    $date1 = $_POST['startdate'];
    $date2 = $_POST['enddate'];
}
$this->inf .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type='date' id='startdate' name='startdate' value='".$date1."'> - <input type='date' id='enddate' name='enddate' value='".$date2."'> <input type='button' name='today' id='today' value='Сегодня' /><br>";

$this->inf .= "<br><input type='submit' name='showlist' value='Показать' />";
$this->inf .= "</form></div>";


// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] == "POST" ) {
$arr_lists = "'2', '3', '4'";

$querystring = "SELECT * FROM manual WHERE date >= ".strtotime($_POST['startdate'])." AND date <= ".(strtotime($_POST['enddate'])+86400)." AND action IN ('2', '3', '4')";
if($_POST['product'] != '' && is_numeric($_POST['product'])) { 
    if($_POST['product'] > 999999999) {
        $querystring .= " AND product = ".$_POST['product']; 
    } else if($_POST['product'] > 999 && $_POST['product']< 10000) {
        $querystring .= " AND (product = 300".$_POST['product']."000 OR product = 900".$_POST['product']."000)"; 
    } else if($_POST['product'] > 1000000 && $_POST['product'] < 1000000000) {
        $querystring .= " AND product = ".$_POST['product']."000"; 
    } else {
        $querystring .= " AND product = ".$_POST['product']; 
    }
}
if($_POST['lot'] != '' && is_numeric($_POST['lot'])) { $querystring .= " AND lot = ".$_POST['lot']; }
if($_POST['operator'] != '') { $querystring .= " AND operator = '".$_POST['operator']."'"; }
if($_POST['warehouse'] != '') { $querystring .= " AND (warehouse1 = '".$_POST['warehouse']."' OR warehouse2 = '".$_POST['warehouse']."')"; }
$querystring .= " ORDER BY date DESC LIMIT 1000";



$querystring = "SELECT product, SUM(quantity1) AS summ1, SUM(quantity2) AS summ2 FROM manual WHERE date >= ".strtotime($_POST['startdate'])." AND date <= ".(strtotime($_POST['enddate'])+86400)." AND action IN ('2', '3', '4') GROUP BY product";


$results = $mysqli->query($querystring);

$this->inf .= "<div class='table'>";
$this->inf .= "<div class='line'><div class='caption'>Изделие</div><div class='caption'>Изменение</div></div>";

while($row = $results->fetch_assoc()) {
//print_r($row);
    $product = substr($row['product'],0,3)."<span class='big'>".substr($row['product'],3,4)."</span>".substr($row['product'],7,3);
    $cause = str_replace("[!=", "<span class='alert'>", $row['cause']);
    $cause = str_replace("=!]", "</span>", $cause);
    if($row['quantity1'] == $row['quantity2']) { $quantity = $row['quantity1'];
    } else { $quantity = $row['quantity1']." &#10144; ".$row['quantity2']; }
    
    if($row['warehouse1'] == $row['warehouse2']) { $warehouse = "<span class='warehouse'>".$row['warehouse1']."</span>";
    } else { $warehouse = "<span class='warehouse'>".$row['warehouse1']."</span> &#10144; <span class='warehouse'>".$row['warehouse2']."</span>"; }

    $action= "0";
    if(($row['summ2']-$row['summ1'])>0) $action= "2";
    if(($row['summ2']-$row['summ1'])<0) $action= "3";
    $this->inf .= "<div class='line action".$action."'>";
    
    $this->inf .= "<div class='cell product'>".$product."</div>";    
    //$this->inf .= "<div class='cell'>".$row['summ2']." - ".$row['summ1']." = ".($row['summ2']-$row['summ1'])."</div>";
    $this->inf .= "<div class='cell'><form action='/man' method='post' target='_blank' style='margin:0px'><input type='hidden' id='startdate' name='startdate' value='".$date1."'><input type='hidden' id='enddate' name='enddate' value='".$date2."'><input type='hidden' id='product' name='product' value='".$row['product']."' /><input type='hidden'  name='showtypes[]' value='2' /><input type='hidden'  name='showtypes[]' value='3' /><input type='hidden'  name='showtypes[]' value='4' /><input type='submit' name='showlist' value='";
    if(round($row['summ2']-$row['summ1'],2)>0) $this->inf .= "+";
    $this->inf .= round($row['summ2']-$row['summ1'],2)."' style='width:150px;height:25px;font-size:16px;font-family:sans-serif;border:none;cursor:pointer;' class='action".$action."'/></form></div>";
    



   $this->inf .= "</div>";
}
$this->inf .= '</div>';
$results->free();

}













?>