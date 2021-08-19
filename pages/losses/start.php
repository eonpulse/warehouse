<?php
$this->inf = '';
mb_internal_encoding("UTF-8");
include('getlogs.php');
$mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
//$statuses = array("","Успешно","Повторно","Нет материала","Нет наряда","Наряд заблокирован","Списан брак");

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
$this->inf .= "<label>Партия <input type='input' id='lot' name='lot' value='";
if(isset($_POST['lot'])) { $this->inf .= $_POST['lot']; }
$this->inf .= "' /></label>";

$this->inf .= "<br><input type='button' name='clear' id='clear' value='Очистить' /> ";
$this->inf .= " <input type='submit' name='showlist' value='Показать' />";
$this->inf .= "</form></div>";







// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] == "POST" ) {


$arr = $_POST[showtypes];
$arr_lists = '\'' . implode ( "','", $arr ) . '\''; //разбиваем массив с одинарными ковычками и запятой + ставим эти кавычки по краям

$querystring = "SELECT product, lot, box FROM messages WHERE date >= ".strtotime($_POST['startdate'])." AND date <= ".(strtotime($_POST['enddate'])+86400);
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
if($_POST['lot'] != '') {
    $querystring .= " AND lot = ".$_POST['lot'];
}

$querystring .= " GROUP BY product, lot, box";
$querystring .= " ORDER BY product ASC, lot ASC, box ASC";
//echo $querystring;
$results = $mysqli->query($querystring);

$this->inf .= "<div class='table'>";
$this->inf .= "<div class='line'><div class='caption'>Изделие</div><div class='caption'>Партия</div><div class='caption'>Коробка</div><div class='caption'>Подрбно</div></div>";

$curproduct = '';
$curlot = '';
$firstbox = 0;
$lastbox = 0;
$oldbox = 0;

while($row = $results->fetch_assoc()) {
//echo $row['product']." - ".$row['lot']." - ".$row['box']."<br>";

    // новая строка
    if($curlot != $row['lot']) {
        if($curlot != '' && $curlot != 0) {
            if($firstbox != 1) {
                if(($oldbox + 1) == ($firstbox - 1)) { $boxes .= "<span class='lost'>".($oldbox + 1)."</span>"; } else { $boxes .= "<span class='lost'>".($oldbox + 1)." - ".($firstbox - 1)."</span>"; }
                $oldbox = $lastbox;
            }
            if($firstbox == $lastbox) { $boxes .= "<span class='good'>".$firstbox."</span>"; } else $boxes .= "<span class='good'>".$firstbox." - ".$lastbox."</span>";
            //вывод
            $product = substr($curproduct,0,3)."<span class='big'>".substr($curproduct,3,4)."</span>".substr($curproduct,7,3);
            $this->inf .= "<div class='line'>";
            $this->inf .= "<div class='cell product'>".$product."</div>";    
            $this->inf .= "<div class='cell lot'><form action='/losses' method='post' target='_blank' style='margin:0px'><input type='hidden' id='startdate' name='startdate' value='2020-01-01'><input type='hidden' id='enddate' name='enddate' value='".$date2."'><input type='hidden' id='product' name='product' value='".$curproduct."' /><input type='hidden'  name='showtypes[]' value='1' /><input type='hidden'  name='showtypes[]' value='2' /><input type='hidden'  name='showtypes[]' value='3' /><input type='hidden'  name='showtypes[]' value='4' /><input type='hidden' id='lot' name='lot' value = '".$curlot."'/><input type='submit' class = 'lotbtn' name='showlist' value='".$curlot."'/></form></div>";
            

            
            
            
            
            $this->inf .= "<div class='cell'>".$boxes."</div>";
            $this->inf .= "<div class='cell'><form action='/terminal' method='post' target='_blank' style='margin:0px'><input type='hidden' id='startdate' name='startdate' value='".$date1."'><input type='hidden' id='enddate' name='enddate' value='".$date2."'><input type='hidden' id='product' name='product' value='".$curproduct."' /><input type='hidden'  name='showtypes[]' value='1' /><input type='hidden'  name='showtypes[]' value='2' /><input type='hidden'  name='showtypes[]' value='3' /><input type='hidden'  name='showtypes[]' value='4' /><input type='hidden' id='lot' name='lot' value = '".$curlot."'/><input type='submit' class = 'lotbtn' name='showlist' value='>>>'/></form></div>";
            
            $this->inf .= "</div>";
        }
        $curproduct = $row['product'];
        $curlot = $row['lot'];
        $firstbox = $row['box'];
        $lastbox = $row['box'];
        $boxes = '';
        $oldbox = 0;
    } else {
        if($row['box'] ==  ($lastbox+1)) { 
            $lastbox = $row['box']; 
            
        } else {
            if($firstbox != 1) {
                if(($oldbox + 1) == ($firstbox - 1)) { $boxes .= "<span class='lost'>".($oldbox + 1)."</span>"; } else { $boxes .= "<span class='lost'>".($oldbox + 1)." - ".($firstbox - 1)."</span>"; }
            }
            $oldbox = $lastbox;
            
        
        
            if($firstbox == $lastbox) { $boxes .= "<span class='good'>".$firstbox."</span>"; } else $boxes .= "<span class='good'>".$firstbox." - ".$lastbox."</span>";
            $firstbox = $row['box'];
            $lastbox = $row['box'];
            
        }
    }
}

// выводим последню строку
if($curlot != '' && $curlot != 0) {
    if($firstbox != 1) {
        if(($oldbox + 1) == ($firstbox - 1)) { $boxes .= "<span class='lost'>".($oldbox + 1)."</span>"; } else { $boxes .= "<span class='lost'>".($oldbox + 1)." - ".($firstbox - 1)."</span>"; }
        $oldbox = $lastbox;
    }
    if($firstbox == $lastbox) { $boxes .= "<span class='good'>".$firstbox."</span>"; } else $boxes .= "<span class='good'>".$firstbox." - ".$lastbox."</span>";
    //вывод
    $product = substr($curproduct,0,3)."<span class='big'>".substr($curproduct,3,4)."</span>".substr($curproduct,7,3);
    $this->inf .= "<div class='line'>";
    $this->inf .= "<div class='cell product'>".$product."</div>";    
    $this->inf .= "<div class='cell lot'><form action='/terminal' method='post' target='_blank' style='margin:0px'><input type='hidden' id='startdate' name='startdate' value='2020-01-01'><input type='hidden' id='enddate' name='enddate' value='".$date2."'><input type='hidden' id='product' name='product' value='".$curproduct."' /><input type='hidden'  name='showtypes[]' value='1' /><input type='hidden'  name='showtypes[]' value='2' /><input type='hidden'  name='showtypes[]' value='3' /><input type='hidden'  name='showtypes[]' value='4' /><input type='hidden' id='lot' name='lot' value='".$curlot."' /><input type='submit' class = 'lotbtn' name='showlist' value='".$curlot."'/></form></div>";
  
    $this->inf .= "<div class='cell'>".$boxes."</div>";
    
    $this->inf .= "<div class='cell'><form action='/terminal' method='post' target='_blank' style='margin:0px'><input type='hidden' id='startdate' name='startdate' value='".$date1."'><input type='hidden' id='enddate' name='enddate' value='".$date2."'><input type='hidden' id='product' name='product' value='".$curproduct."' /><input type='hidden'  name='showtypes[]' value='1' /><input type='hidden'  name='showtypes[]' value='2' /><input type='hidden'  name='showtypes[]' value='3' /><input type='hidden'  name='showtypes[]' value='4' /><input type='hidden' id='lot' name='lot' value = '".$curlot."'/><input type='submit' class = 'lotbtn' name='showlist' value='>>>'/></form></div>";
    
    $this->inf .= "</div>";
}



$this->inf .= '</div>';
$results->free();

}













?>