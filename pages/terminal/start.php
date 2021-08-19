<?php
$this->inf = '';
mb_internal_encoding("UTF-8");
include('getlogs.php');
$mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
$statuses = array("","Успешно","Повторно","Нет материала","Нет наряда","Наряд заблокирован","Списан брак");

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
if(!isset($_POST['showlist'])) {
    for($i=1; $i<count($statuses); $i++) {
        $this->inf .= "<label><input type='checkbox' name='showtypes[]' value='".$i."' checked>".$statuses[$i]."</label>";
    }
} else {
    $arr = $_POST[showtypes];
    for($i=1; $i<count($statuses); $i++) {
        $this->inf .= "<label><input type='checkbox' name='showtypes[]' value='".$i."'";
        if(in_array($i, $arr)) $this->inf .= " checked";
        $this->inf .= ">".$statuses[$i]."</label>";
    }
}
$this->inf .= "<br><label>Изделие <input type='input' id='product' name='product' value='";
if(isset($_POST['product'])) { $this->inf .= $_POST['product']; }
$this->inf .= "' /></label>";
$this->inf .= " <label>Партия <input type='input' id='lot' name='lot' value='";
if(isset($_POST['lot'])) { $this->inf .= $_POST['lot']; }
$this->inf .= "' /></label>";
$this->inf .= " <label>Оператор <input type='input' id='terminal' name='terminal' value='";
if(isset($_POST['terminal'])) { $this->inf .= $_POST['terminal']; }
$this->inf .= "' /></label>";
$this->inf .= "<br><input type='button' name='clear' id='clear' value='Очистить' /> ";
$this->inf .= " <input type='submit' name='showlist' value='Показать' />";
$this->inf .= "</form></div>";



// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] == "POST" ) {


$arr = $_POST[showtypes];
$arr_lists = '\'' . implode ( "','", $arr ) . '\'';

$querystring = "SELECT * FROM messages WHERE date >= ".strtotime($_POST['startdate'])." AND date <= ".(strtotime($_POST['enddate'])+86400)." AND status IN (".$arr_lists.")";
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
if($_POST['terminal'] != '') { $querystring .= " AND terminal = '".$_POST['terminal']."'"; }
$querystring .= " ORDER BY date DESC";

$results = $mysqli->query($querystring);

$this->inf .= "<div class='table'>";
$this->inf .= "<div class='line'><div class='caption'>Дата</div><div class='caption'>Изделие</div><div class='caption'>Партия</div><div class='caption'>Коробка</div><div class='caption'>Оператор</div><div class='caption'>Статус</div></div>";

while($row = $results->fetch_assoc()) {
    $product = substr($row['product'],0,3)."<span class='big'>".substr($row['product'],3,4)."</span>".substr($row['product'],7,3);
    $this->inf .= "<div class='line status".$row['status']."'>";
    $this->inf .= "<div class='cell'>".date('d.m.Y H:i:s', $row['date'])."</div>";
    $this->inf .= "<div class='cell product'>".$product."</div>";    
    $this->inf .= "<div class='cell lot'>".$row['lot']."</div>";
    $this->inf .= "<div class='cell'>".$row['box']."</div>";
    $this->inf .= "<div class='cell terminal'>".$row['terminal']."</div>";
    if($row['status']==1) { $this->inf .= "<div class='cell'>Изделие успешно оприходовано в количестве ".$row['quantity'].". Наряд ".$row['note']."</div>"; }
    else if($row['status']==2) { $this->inf .= "<div class='cell'>Коробка ".$row['note']." уже была оприходована ранее в количестве ".$row['quantity'].".</div>"; }
    else if($row['status']==3) { $this->inf .= "<div class='cell'>Невозможно оприходовать ".$row['quantity']." шт. Недостаточно материала ".$row['note']."</div>"; } 
    else if($row['status']==4) { $this->inf .= "<div class='cell'>Невозможно оприходовать коробку ".$row['note'].". Нет ни одного производственного наряда</div>"; }
    else if($row['status']==5) { $this->inf .= "<div class='cell'>К производственному наряду ".$row['note']." открыто подтверждение о изготовлениии. Пожалуйста устраните причину блокировки и попробуйте снова.</div>"; } 
    else if($row['status']==6) { $this->inf .= "<div class='cell'>Списан брак в количестве ".$row['quantity']." шт.</div>"; }             
    else { $this->inf .= "<div class='cell'>".$statuses[($row['status'])]."</div>"; }



   $this->inf .= "</div>";
}
$this->inf .= '</div>';
$results->free();

}




?>