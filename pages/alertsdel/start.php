<?php
$this->inf = '';
mb_internal_encoding("UTF-8");
include('getlogs.php');
$mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
$statuses = array("","Успешно","Повторно","Нет материала","Нет наряда","Наряд заблокирован","Списан брак");



$querystring = "SELECT * FROM alerts ORDER BY date DESC, product ASC, lot ASC, box ASC";
$results = $mysqli->query($querystring);


$el = array();
while($row = $results->fetch_assoc()) {
    $added = 0;
    foreach($el as $id => $element) {
        if($element['product'] == $row['product']) {
            if(!in_array($row['lot'], $el[$id]['lot'])) {
                array_push($el[$id]['lot'], $row['lot']);
                $el[$id]['box'][($row['lot'])] = array($row['box']);
                sort($el[$id]['box'][($row['lot'])]);
            } else {
                array_push($el[$id]['box'][($row['lot'])], $row['box']);
                sort($el[$id]['box'][($row['lot'])]);
            }
            
            $added = 1;
        }
    }
    if($added == 0) {
        $el[] = array('product' => $row['product'], 'date' => $row['date'], 'status' => $row['status'], 'note' => $row['note'], 'lot' => array($row['lot']), 'box' => array(($row['lot']) => array($row['box'])));
    }
}


$this->inf .= "<div class='table'>";
$this->inf .= "<div class='line'><div class='caption'>Дата</div><div class='caption'>Изделие</div><div class='caption'>Статус</div><div class='caption'>[Партия] Коробки</div></div>";
foreach($el as $element) {
    if((time() - $element['date'] > 43200)) { $color = 'orange'; }
    if((time() - $element['date'] > 172800)) { $color = 'red'; }
    if((time() - $element['date'] < 43201)) { $color = 'ellow'; }

    $this->inf .= "<div class='line status_".$color."'>";
    if($color == 'ellow') { $this->inf .= "<div class='cell'>".date('H:i:s', $element['date'])."</div>"; }
    else { $this->inf .= "<div class='cell'>".date('d.m.Y', $element['date'])."</div>"; }
    $product = substr($element['product'],0,3)."<span class='big'>".substr($element['product'],3,4)."</span>".substr($element['product'],7,3);
    $this->inf .= "<div class='cell'>".$product."</div>";
    
    if($element['status']==3) { 
        $material = substr($element['note'],0,3)."<span class='big'>".substr($element['note'],3,4)."</span>".substr($element['note'],7,3);
        $this->inf .= "<div class='cell'>Недостаточно материала ".$material."</div>"; 
    } 
    else if($element['status']==4) { $this->inf .= "<div class='cell'>Нет производственного наряда</div>"; }
    else if($element['status']==5) { $this->inf .= "<div class='cell'>К производственному наряду ".$element['note']." открыто подтверждение о изготовлениии</div>"; } 
    else { $this->inf .= "<div class='cell'>".$element['status']."</div>"; }    
    
    $this->inf .= "<div class='cell'>";
    foreach($element['lot'] as $lot) {
        $this->inf .= "[".$lot."] ";
        //$this->inf .= "<div>";
        foreach($element['box'][$lot] as $box) {
            $this->inf .= " &nbsp;<div class='dellink' data-product='".$element['product']."' data-lot='".$lot."' data-box='".$box."' data-note='".$element['note']."'>.".$box.".</div>&nbsp; ";
        }
        //$this->inf .= "</div>";
        $this->inf .= "<br>";
    }
     $this->inf .= "</div></div>";
}
$results->free();














?>