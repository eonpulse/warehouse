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
$order = "";
//print_r($el);
if(count($el)>0) {

    $this->inf .= "<div class='table'>";
    $this->inf .= "<div class='line'><div class='caption'>Дата</div><div class='caption'>Изделие</div><div class='caption'>Статус</div><div class='caption'>[Партия] Коробки</div></div>";
    foreach($el as $element) {
        if((time() - $element['date'] > 43200)) { $color = 'orange'; }
        if((time() - $element['date'] > 172800)) { $color = 'red'; }
        if((time() - $element['date'] < 43201)) { $color = 'ellow'; }
    
        $this->inf .= "<div class='line status_".$color."'>";
        if($color == 'ellow') { $this->inf .= "<div class='cell'>".date('H:i:s', $element['date']); }
        else { $this->inf .= "<div class='cell'>".date('d.m.Y', $element['date']); }
        $this->inf .= "<form method='POST' action='barcode' TARGET='_blank'><input type='hidden' name='product' value='".$element['product']."'><input type='submit' name='showlist' value='Штрих-код'></form>";
        $this->inf .= "</div>";
        
        $product = "<span class='product'>".substr($element['product'],0,3)."<span class='big'>".substr($element['product'],3,4)."</span>".substr($element['product'],7,3)."</span>";
        $this->inf .= "<div class='cell'>".$product."</div>";
        
        if($element['status']==3) { 
            $material = "<span class='product'>".substr($element['note'],0,3)."<span class='big'>".substr($element['note'],3,4)."</span>".substr($element['note'],7,3)."<span class='product'>";
            //$material = $element['note'];
            $this->inf .= "<div class='cell'>Недостаточно материала ".$material."</div>"; 
        } 
        else if($element['status']==4) { $this->inf .= "<div class='cell'>Нет производственного наряда</div>"; }
        else if($element['status']==5) { 
            $this->inf .= "<div class='cell'>К производственному наряду <span class='product'>".$element['note']."<span class='product'> открыто подтверждение о изготовлениии</div>";
            $order = $element['note'];
        } 
        else { $this->inf .= "<div class='cell'>".$element['status']."</div>"; }    
        
        $this->inf .= "<div class='cell'>";
        foreach($element['lot'] as $lot) {
            $this->inf .= "[".$lot."] ";
            foreach($element['box'][$lot] as $box) {
                $this->inf .= " &nbsp;".$box."&nbsp; ";
            }
            $this->inf .= "<br>";
        }
         $this->inf .= "</div></div>";
    }



}else {
    $this->inf .= "<br><br><br><center><div class='bprlogo'></div><div class='time'>".date("H:i")."</div><div class='date'>".date("d.m.Y")."</div><h1>Нет ошибок оприходования</h1></center>";
}
if($order != "") {
    $this->inf .= "<div class='popup'>";
    $this->inf .= "<div class='header'>&nbsp; К производственному наряду открыто подтверждение о изготовлениии";
    $this->inf .= "<a href='help_start_order' target='blank'><div class='helpbutton'>?</div></a><div class='closebutton'>X</div></div>";
    $this->inf .= "<p>К производственному наряду <span class='product'>".$order."</span> открыто подтверждение о изготовлениии.</p><p>Для устранения данной ошибки нужно активировать наряд в производственном центре и вручную списать комплектующие. Подробная инструкция дана в справке.</p>";
    $this->inf .= "<br><br><center><a href='#' data-product='0' data-lot='0' data-box='0' data-note='".$order."' class='donebutton'>Сделано. Больше не показывать</a></center></div>";
}
$results->free();














?>