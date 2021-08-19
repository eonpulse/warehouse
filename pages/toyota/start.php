<?php
$this->inf = '';
mb_internal_encoding("UTF-8");
$mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}	    

include('operators.php');


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

$this->inf .= "<label>Изделие <input type='input' id='product' name='product' value='";
if(isset($_POST['product'])) { $this->inf .= $_POST['product']; }
$this->inf .= "' /></label>";
$this->inf .= " <label>Оператор <input type='input' id='user' name='user' value='";
if(isset($_POST['user'])) { $this->inf .= $_POST['user']; }
$this->inf .= "' /></label>";
$this->inf .= "<br><input type='button' name='clear' id='clear' value='Очистить' /> ";
$this->inf .= " <input type='submit' name='showlist' value='Показать' />";
$this->inf .= "</form></div>";



// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] == "POST" ) {
    $querystring = "SELECT *, COUNT(DISTINCT product, lot, box) AS prod_count FROM toyota WHERE time >= '".$_POST['startdate']."T00:00:00' AND time <= '".$_POST['enddate']."T23:59:59'";
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
    if($_POST['user'] != '') { $querystring .= " AND user = '".$_POST['user']."'"; }
    $querystring .= " GROUP BY product, lot, box WITH ROLLUP";
//    $querystring .= " ORDER BY time ASC";
    //echo $querystring;
    $results = $mysqli->query($querystring);    
    
    
$this->inf .= "<div>";


$lastproduct = '';
while($row = $results->fetch_assoc()) {
    if($row['product'] != $lastproduct) {
        if($lastproduct != '') $this->inf .= "</div>";
        $this->inf .= "<div class='frame'>";
        if($row['product']) {
            $this->inf .= "<div class='table' prod='".$row['product']."'><div class='line'><div class='caption'>Дата</div><div class='caption'>Партия</div><div class='caption'>Коробка</div><div class='caption'>Количество</div><div class='caption'>Оператор</div></div>";    
        }

    }
    if($row['box'] == '' && $row['lot'] == 0) {
        
        if($row['product']) {
            $product = substr($row['product'],0,3)."<span class='big'>".substr($row['product'],3,4)."</span>".substr($row['product'],7,3);
            $this->inf .= "</div><div class='product'>".$product."</div>"; 
        } else {
            $this->inf .= "<div class='product'><b>Всего</b></div>"; 
        }
        $this->inf .= "<div class='count'>".$row['prod_count']."</div>";  
    } elseif($row['box'] != '' && $row['lot'] != 0) {
        $this->inf .= "<div class='line'>";
        $this->inf .= "<div class='cell'>".$row['time']."</div>";
        $this->inf .= "<div class='cell lot'>".$row['lot']."</div>";
        $this->inf .= "<div class='cell'>".$row['box']."</div>";
        $this->inf .= "<div class='cell'>".$row['quantity']."</div>";
        
        $operatorname = $row['user'];
        foreach ($operators as $operator) {
            if($operator[0] == $row['user']) {
                $operatorname = $operator[1];
            }
        }
        
        
        $this->inf .= "<div class='cell terminal'>".$operatorname."</div>";
        $this->inf .= "</div>";
        
    }
    $lastproduct = $row['product'];
}

$this->inf .= '</div>';
$results->free();    
   
    
}    
    
    
    
    
    




?>