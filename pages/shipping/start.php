<?php
$this->inf = '';
mb_internal_encoding("UTF-8");


//print_r($this->url);
switch ($this->url[2]) {
    case "manage":
        $this->inf .= manager($this->url);
        break;
    case "show":
        $this->inf .= show();
        break;
    case "export":
        $this->inf .= export();
        break;
    case "statistics":
        echo "статистика";
        break;
    default:
        $this->inf .= show();
}


// ========== M A N A G E ========== //
function manager($url) {
    $mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }


    if(isset($_POST['delbtn'])) {
        $query = "DELETE FROM shipping_loading WHERE id=? LIMIT 1";
        $statement = $mysqli->prepare($query);
        $statement->bind_param('i', $_POST['shippingid']);
        if($statement->execute()){
            //print 'Коробка успешно удалена';
        }else{
            //echo $str."<br>";
            die('ALARM UPDATE Error : ('. $mysqli->errno .') '. $mysqli->error);
        }
        $statement->close();
    } 




    //===== Добавление новой строки ======
    if(isset($_POST['additem'])) { 
        
        if(empty($_POST['client'])) { 
            //$inf .= "Клиент не указан";
        } else {
            $clientid = checkclient($_POST['client']);
        }
        
        if(empty($_POST['driver'])) { 
            $driverid = 0;
        } else {
            $driverid = checkdriver($clientid, $_POST['driver']);
        }
        
        if(empty($_POST['auto'])) { 
            $autoid = 0;
        } else {
            $autoid = checkauto($_POST['auto']);
        }
        
        if($_POST['client'])
        $querystring = "SELECT * FROM shipping_clients WHERE `name`='".$_POST['client']."'";
        $results = $mysqli->query($querystring);
        $count =  $results->num_rows;
        if($count > 0) { 
            $row = $results->fetch_assoc();
            $clientid = $row['id'];
            //$inf .= "Клиент ".$clientid." уже есть"; 
        } else { 
            $query = "INSERT INTO shipping_clients (name) VALUES (?)";
            $statement = $mysqli->prepare($query);
            //s = string, i = integer, d = double,  b = blob
            $statement->bind_param('s', $_POST['client']);
            if($statement->execute()){
                 $clientid = $statement->insert_id;
                //$inf .= "Добавили клиента ".$clientid; 
            }else{
            die('MESSAGE Error : ('. $mysqli->errno .') '. $mysqli->error);
            }
            $statement->close();
                
        }
   
        


        $query = "INSERT INTO shipping_loading (client, driver, auto, time_arrival_plan, time_finish_plan) VALUES (?, ?, ?, ?, ?)";
        $statement = $mysqli->prepare($query);
        //s = string, i = integer, d = double,  b = blob
        $statement->bind_param('iiiss', $clientid, $driverid, $autoid, $_POST['arrival_plan'], $_POST['finish_plan']);
        echo $_POST['shipping_plan'];
        if($statement->execute()){
            //$inf .= "Добавили строку"; 
        }else{
            die('MESSAGE Error : ('. $mysqli->errno .') '. $mysqli->error);
        }
        $statement->close();
    }
    //----- Добавление новой строки -----


    //===== Текущее время прибытия =====
    if(isset($_POST['setarrival'])) {
        $querystring = "SELECT * FROM shipping_loading WHERE `id`='".$_POST['shippingid']."' AND time_arrival_fact";
        $results = $mysqli->query($querystring);
        $count =  $results->num_rows;
        if($count == 0 ) { 
            $query = "UPDATE  shipping_loading SET time_arrival_fact=? WHERE id=?";
            $statement = $mysqli->prepare($query);
            //s = string, i = integer, d = double,  b = blob
            $statement->bind_param('si', date('Y-m-d H:i'), $_POST['shippingid']);
            if($statement->execute()){
    
            }else{
                die('MESSAGE Error : ('. $mysqli->errno .') '. $mysqli->error);
            }
            $statement->close(); 
        }  else {
            echo "<script>alert('Данные уже были изменены другим оператором!');</script>";
        }      
    }

    //===== Текущее время отправления =====
    if(isset($_POST['setfinish'])) {
        $querystring = "SELECT * FROM shipping_loading WHERE `id`='".$_POST['shippingid']."' AND time_finish_fact";
        $results = $mysqli->query($querystring);
        $count =  $results->num_rows;
        if($count == 0 ) { 
        
            $query = "UPDATE  shipping_loading SET time_finish_fact=? WHERE id=?";
            $statement = $mysqli->prepare($query);
            //s = string, i = integer, d = double,  b = blob
            $statement->bind_param('si', date('Y-m-d H:i'), $_POST['shippingid']);
            if($statement->execute()){
    
            }else{
                die('MESSAGE Error : ('. $mysqli->errno .') '. $mysqli->error);
            }
            $statement->close();   
            
            
            $querystring = "SELECT TIME_TO_SEC(timediff(time_arrival_fact, time_arrival_plan)) AS diffarrival, TIME_TO_SEC(timediff(time_finish_fact, time_finish_plan)) AS difffinish FROM shipping_loading WHERE shipping_loading.id=".$_POST['shippingid']." LIMIT 1";
            $results = $mysqli->query($querystring);
            $row = $results->fetch_assoc();
          
            if($row['difffinish']<0) {
                //если время погрузки фактическое меньше планового - статус ОК
                $query = "UPDATE  shipping_loading SET status=1 WHERE id=?";
                $statement = $mysqli->prepare($query);
                //s = string, i = integer, d = double,  b = blob
                $statement->bind_param('i', $_POST['shippingid']);
                if($statement->execute()){
        
                }else{
                    die('MESSAGE Error : ('. $mysqli->errno .') '. $mysqli->error);
                }
                $statement->close(); 
            } else if($row['diffarrival']>0) {
                $query = "UPDATE  shipping_loading SET status=3, cause=1 WHERE id=?";
                $statement = $mysqli->prepare($query);
                //s = string, i = integer, d = double,  b = blob
                $statement->bind_param('i', $_POST['shippingid']);
                if($statement->execute()){
        
                }else{
                    die('MESSAGE Error : ('. $mysqli->errno .') '. $mysqli->error);
                }
                $statement->close();        
            
            
            
            
                //иначе если время прибытия фактическое больше плановое - статус НОК и причина опоздание
                 
            }
        }  else {
            echo "<script>alert('Данные уже были изменены другим оператором!');</script>";
        }            
    }



    //===== Изменение строки =====
    if(isset($_POST['editbtn'])) {
        if(empty($_POST['client'])) { 
            $inf .= "Клиент не указан";
        } else {
            $clientid = checkclient($_POST['client']);
        }
        
        if(empty($_POST['driver'])) { 
            $driverid = 0;
        } else {
            $driverid = checkdriver($clientid, $_POST['driver']);
        }
        
        if(empty($_POST['auto'])) { 
            $autoid = 0;
        } else {
            $autoid = checkauto($_POST['auto']);
        }
        
        if(empty($_POST['cause'])) { 
            $causeid = NULL;
        } else {
            $causeid = checkcause($_POST['cause']);
        }
        
        if(empty($_POST['arrival_fact']))  $_POST['arrival_fact'] = NULL;
        if(empty($_POST['finish_fact']))  $_POST['finish_fact'] = NULL;
        
        $query = "UPDATE  shipping_loading SET client=?, driver=?, auto=?, time_arrival_plan=?, time_arrival_fact=?, time_finish_plan=?, time_finish_fact=?, status=?, cause=? WHERE id=?";
        $statement = $mysqli->prepare($query);
        //s = string, i = integer, d = double,  b = blob
        $statement->bind_param('iiissssiii', $clientid, $driverid, $autoid, $_POST['arrival_plan'], $_POST['arrival_fact'], $_POST['finish_plan'], $_POST['finish_fact'], $_POST['status'], $causeid, $_POST['shippingid']);
        if($statement->execute()){

        }else{
            die('MESSAGE Error : ('. $mysqli->errno .') '. $mysqli->error);
        }
        $statement->close();        
    }






    //Редактирование строки
    if(!empty($url[3])) {
        $querystring = "SELECT shipping_clients.name, shipping_drivers.fio, shipping_auto.number, shipping_loading.* FROM shipping_loading LEFT JOIN shipping_clients on shipping_loading.client = shipping_clients.id LEFT JOIN shipping_drivers ON shipping_loading.driver = shipping_drivers.id LEFT JOIN shipping_auto ON shipping_loading.auto = shipping_auto.id WHERE shipping_loading.id=".$url[3]." ORDER BY shipping_loading.id DESC LIMIT 1";
        $results = $mysqli->query($querystring);
        $row = $results->fetch_assoc();
        $loadingid = $url[3];
        $client = $row['name'];
        $driver = $row['fio'];
        $arrival_plan = $row['time_arrival_plan'];
        $arrival_fact = $row['time_arrival_fact'];
        $finish_plan = $row['time_finish_plan'];
        $finish_fact = $row['time_finish_fact'];
    }







    $inf .= "<table class='manage'><tr><td>Клиент</td><td>Время прибытия</td><td>Время отправления</td><td>Водитель</td><td>№ авто</td><td>Статус</td><td>Причина задержки</td><td style='width: 80px;'> </td></tr>";
    
    $querystring = "SELECT shipping_clients.name, shipping_drivers.fio, shipping_auto.number, shipping_cause.cause AS causename, shipping_loading.*, TIME_TO_SEC(timediff(time_arrival_fact, time_arrival_plan)) AS diffarrival, TIME_TO_SEC(timediff(time_finish_fact, time_finish_plan)) AS difffinish FROM shipping_loading LEFT JOIN shipping_clients on shipping_loading.client = shipping_clients.id LEFT JOIN shipping_drivers ON shipping_loading.driver = shipping_drivers.id LEFT JOIN  shipping_cause ON shipping_loading.cause = shipping_cause.id LEFT JOIN shipping_auto ON shipping_loading.auto = shipping_auto.id WHERE shipping_loading.time_arrival_plan >= '".date('Y-m-d 00:00', microtime(true)-(86400))."' ORDER BY shipping_loading.time_arrival_plan ASC";
    $results = $mysqli->query($querystring);
    while($row = $results->fetch_assoc()) {
        if($loadingid == $row['id']) {
            //Правка строки
            $inf .= "<tr><form action='/shipping/manage' method='post'><input type='hidden' name='shippingid' value='".$row['id']."'>";
            $inf .= "<td><input list='client' name='client' autocomplete='off' value='".$row['name']."' required></td>";
            $inf .= "<td><input type='datetime-local' id='arrival_plan' name='arrival_plan' value='".str_replace(" ", "T", $row['time_arrival_plan'])."' />
                     <br><input type='datetime-local' id='arrival_fact' name='arrival_fact' value='".str_replace(" ", "T", $row['time_arrival_fact'])."' /></td>";
            $inf .= "<td><input type='datetime-local' id='finish_plan' name='finish_plan' value='".str_replace(" ", "T", $row['time_finish_plan'])."' />
                     <br><input type='datetime-local' id='finish_fact' name='finish_fact' value='".str_replace(" ", "T", $row['time_finish_fact'])."' /></td>";
            $inf .= "<td><input list='driver' name='driver' autocomplete='off' value='".$row['fio']."'></td>";
            $inf .= "<td><input list='auto' name='auto' autocomplete='off' value='".$row['number']."'></td>";
            $inf .= "<td><select name='status'><option value='0'></option><option value='1'";
            if($row['status'] == 1) $inf .= " selected";
            $inf .= ">OK</option><option value='2'";
            if($row['status'] == 2) $inf .= " selected";
            $inf .= ">Проблемы</option><option value='3'";
            if($row['status'] == 3) $inf .= " selected";
            $inf .= " >NOK</option></select></td>";
            $inf .= "<td><input list='cause' name='cause' autocomplete='off' value='".$row['causename']."'></td>";
            $inf .= "<td><a href='/shipping/manage'><div class='imgbutton undo' title='Назад'></div></a><input type='submit' class='imgbutton save' name='editbtn' value=' ' title='Сохранить изменения'></div></td>";
            $inf .= "</form><tr>";
        } else {
            //Обычная строка
            $inf .= "<tr";
            if($lastday != date('d', strtotime($row['time_arrival_plan']))) $inf .= " class='newday'";
            $inf .= "><form action='' method='post'><input type='hidden' name='shippingid' value='".$row['id']."'>";
            $inf .= "<td>".$row['name']."</td>";
            $inf .= "<td>".date('d.m.Y H:i', strtotime($row['time_arrival_plan']))." &nbsp; / &nbsp; ";
            if($row['time_arrival_fact']) { 
                if($row['diffarrival']>0) { $inf .= "<span class='datetime red'>"; } else { $inf .= "<span class='datetime'>"; }
                if(date('d.m.Y', strtotime($row['time_arrival_plan'])) == date('d.m.Y', strtotime($row['time_arrival_fact']))) {
                    $inf .= date('H:i', strtotime($row['time_arrival_fact'])); 
                } else {
                    $inf .= date('d.m.Y H:i', strtotime($row['time_arrival_fact'])); 
                }
                $inf .= "</span>";
            } else {
                $inf .= "<input type='submit' class='imgbutton clock' name='setarrival' value=' ' title='Текущее время'>";
            }
            $inf .= "</td>";
            $inf .= "<td>".date('d.m.Y H:i', strtotime($row['time_finish_plan']))." &nbsp; / &nbsp; ";
            if($row['time_finish_fact']) {
                if($row['difffinish']>0) { $inf .= "<span class='datetime red'>"; } else { $inf .= "<span class='datetime'>"; }
                if(date('d.m.Y', strtotime($row['time_finish_plan'])) == date('d.m.Y', strtotime($row['time_finish_fact']))) {
                    $inf .= date('H:i', strtotime($row['time_finish_fact'])); 
                } else {
                    $inf .= date('d.m.Y H:i', strtotime($row['time_finish_fact'])); 
                }
                $inf .= "</span>";
            } else {
                $inf .= "<input type='submit' class='imgbutton clock' name='setfinish' value=' ' title='Текущее время'>";
            }
            $inf .= "</td>";
            $inf .= "<td>".$row['fio']."</td>";
            $inf .= "<td>".$row['number']."</td>";
            $inf .= "<td><center><div class='status".$row['status']."'></div></center></td>";
            $inf .= "<td>".$row['causename']."</td>";
            $inf .= "<td style='width: 80px;'><a href='/shipping/manage/".$row['id']."'><div class='imgbutton edit' title='Изменить'></div></a><input type='submit' class='imgbutton delete' name='delbtn' data-id='".$row['id']."' data-client='".$row['name']."'  value=' ' title='Удалить'>";
            if($row['status'] =='') { $inf .= "<input type='submit' class='imgbutton ok' name='shippingok' value=' ' title='Машина загружена'></td>"; }
            $inf .= "</form><tr>";
        }
        $lastday = date('d', strtotime($row['time_arrival_plan']));
    }

    //Новая строка
    
    $inf .= "<datalist id='client'>";
    $querystring = "SELECT * FROM shipping_clients ORDER BY name";
    $results = $mysqli->query($querystring);
    while($row = $results->fetch_assoc()) {
        $inf .= "<option value='".$row['name']."'>";
    }
    $inf .= "</datalist>";
    $inf .= "<datalist id='driver'>";
    $querystring = "SELECT * FROM shipping_drivers ORDER BY fio";
    $results = $mysqli->query($querystring);
    while($row = $results->fetch_assoc()) {
        $inf .= "<option value='".$row['fio']."'>";
    }
    $inf .= "</datalist>";
    $inf .= "<datalist id='auto'>";
    $querystring = "SELECT * FROM shipping_auto ORDER BY number";
    $results = $mysqli->query($querystring);
    while($row = $results->fetch_assoc()) {
        $inf .= "<option value='".$row['number']."'>";
    }
    $inf .= "</datalist>";
    $inf .= "<datalist id='cause'>";
    $querystring = "SELECT * FROM shipping_cause ORDER BY cause";
    $results = $mysqli->query($querystring);
    while($row = $results->fetch_assoc()) {
        $inf .= "<option value='".$row['cause']."'>";
    }
    $inf .= "</datalist>";
    
    
    
    
    if(!$loadingid) {
        $inf .= "<tr><form action='' method='post'>";
        $inf .= "<td><input list='client' name='client' autocomplete='off' value='' required></td>";
        $inf .= "<td><input type='datetime-local' id='arrival_plan' name='arrival_plan' value='".date('Y-m-d\TH:00')."' /></td>";
        $inf .= "<td><input type='datetime-local' id='finish_plan' name='finish_plan' value='".date('Y-m-d\TH:00')."' /></td>";
        $inf .= "<td><input list='driver' name='driver' autocomplete='off' value=''></td>";
        $inf .= "<td><input list='auto' name='auto' autocomplete='off' value=''></td>";
        $inf .= "<td></td>";
        $inf .= "<td></td>";
        $inf .= "<td style='width: 80px;'><input type='submit' class='imgbutton add' name='additem' value=' '></td>";
        $inf .= "</form></tr>";
    }

    $inf .= "</table>";
    
    $inf .= "<a class='exportexcel' href='/shipping/export' target='blank'>Выгрузка отчёта в Excel</a>";
    
    return $inf;
}
// ========================= //



// ========== S H O W ========== //
function show() {
    //Подключение к базе
    $mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }

    
    $currtime = date("d.m.Y \&\\n\b\s\p\;\&\\n\b\s\p\; H:i");
    $currtimestamp = strtotime(date("Y-m-d  H:i"));
    $laststatus = 0; $curr = 0;
    $clients = array();
    $inf .= "<meta http-equiv='Refresh' content='60' />";
    $inf .= "<div style='background-color: #000; width: 100%; height: 100%; padding-top: 20'><div class='tv'><div class='logo'></div><div class ='date'>".$currtime."</div><table class='tv'><tr><th style='width: 250px; padding:0;'>КЛИЕНТ</th><th style='width: 200px; padding:0;'>ВРЕМЯ ПРИБЫТИЯ (план/факт)</th><th style='width: 200px; padding:0;'>ВРЕМЯ ОТПРАВЛЕНИЯ (план/факт)</th><th style='width: 230px; padding:0;'>ВОДИТЕЛЬ</th><th style='width: 200px; padding:0;'>№ авто</th><th style='width: 30px; padding:0;'></th><th>ПРИЧИНА ЗАДЕРЖКИ</th></tr>";
    
    $querystring = "SELECT shipping_clients.name, shipping_drivers.fio, shipping_auto.number, shipping_cause.cause AS causename, shipping_loading.*, TIME_TO_SEC(timediff(time_arrival_fact, time_arrival_plan)) AS diffarrival, TIME_TO_SEC(timediff(time_finish_fact, time_finish_plan)) AS difffinish FROM shipping_loading LEFT JOIN shipping_clients on shipping_loading.client = shipping_clients.id LEFT JOIN shipping_drivers ON shipping_loading.driver = shipping_drivers.id LEFT JOIN  shipping_cause ON shipping_loading.cause = shipping_cause.id LEFT JOIN shipping_auto ON shipping_loading.auto = shipping_auto.id  WHERE shipping_loading.time_arrival_plan >= '".date('Y-m-d 00:00')."' AND shipping_loading.time_arrival_plan <= '".date('Y-m-d 23:59')."' ORDER BY shipping_loading.time_arrival_plan ASC";
    $results = $mysqli->query($querystring);
    while($row = $results->fetch_assoc()) {
        $timestamp1 = strtotime($row['time_arrival_plan']);
        $timestamp2 = strtotime($row['time_finish_plan']);
        $clients[] = $row['name'];
        $clienscounts = array_count_values($clients);
        $inf .= "<tr style='height: 26px;'";
        $inf .= " class='";
        if($row['status']>0) { $inf .= " done"; }
        if($currtimestamp > $timestamp1 && $row['time_arrival_fact']==NULL) { $inf .= " blink"; }
        if($laststatus > 0 && $row['status'] == 0 && $curr ==0 && $row['time_finish_fact']==NULL) { $inf .= " current"; $curr = 1; }
        $inf .= "'>";
        if($currtimestamp >= $timestamp1 && $currtimestamp < ($timestamp1+60) && $laststatus == 0) { 
            // сделать дзынь
            $inf .= "<audio src='/sounds/dzin.mp3' autoplay></audio>";
        }
        $inf .= "<td style='width: 250px; padding:0; padding-left: 5;'>".$row['name'];
        if($clienscounts[($row['name'])]>1) { $inf .= " - ".$clienscounts[($row['name'])].""; }
        $inf .= "</td>";
        $inf .= "<td style='text-align: center; width: 200px; padding:0;'>";
        if(date("d.m.Y") != date('d.m.Y', strtotime($row['time_arrival_plan']))) {
            $inf .= date('d.m H:i', strtotime($row['time_arrival_plan']));
        } else {
            $inf .= date('H:i', strtotime($row['time_arrival_plan']));
        }
        $inf .= " &nbsp; / &nbsp; ";
        if($row['time_arrival_fact']) { 
            if($row['diffarrival']>0) { $inf .= "<span class='datetime red'>"; } else { $inf .= "<span class='datetime green'>"; }
            if(date('d.m.Y', strtotime($row['time_arrival_plan'])) == date('d.m.Y', strtotime($row['time_arrival_fact'])) && date("d.m.Y") == date('d.m.Y', strtotime($row['time_arrival_plan']))) {
                $inf .= date('H:i', strtotime($row['time_arrival_fact'])); 
            } else {
                $inf .= date('d.m H:i', strtotime($row['time_arrival_fact'])); 
            }
            $inf .= "</span>";
        }
        $inf .= "</td>";
        $inf .= "<td style='text-align: center; width: 200px; padding:0;'>".date('H:i', strtotime($row['time_finish_plan']))." &nbsp; / &nbsp; ";
        if($row['time_finish_fact']) {
            if($row['difffinish']>0) { $inf .= "<span class='datetime red'>"; } else { $inf .= "<span class='datetime green'>"; }
            if(date('d.m.Y', strtotime($row['time_finish_plan'])) == date('d.m.Y', strtotime($row['time_finish_fact']))) {
                $inf .= date('H:i', strtotime($row['time_finish_fact'])); 
            } else {
                $inf .= date('d.m H:i', strtotime($row['time_finish_fact'])); 
            }
            $inf .= "</span>";
        }
        $inf .= "</td>";
        $inf .= "<td style='width: 200px; padding:0; padding-left: 5;'>".$row['fio']."</td>";
        $inf .= "<td style='width: 100px; padding:0; padding-left: 5;'>".$row['number']."</td>";
        $inf .= "<td style='text-align: center; width: 30px; padding:0;'><center><div class='status".$row['status']."'></div></center></td>";
        $inf .= "<td>".$row['causename']."</td>";
        $inf .= "<tr>";
        $laststatus = $row['status'];
    }
    $inf .= "</table></div></div>";
    return $inf;
}
// ========================= //



// ========== E X P O R T ========== //
function export() {
    if(isset($_POST['exportxlsx'])) {
        require_once 'classes/PHPExcel.php';
        require_once 'classes/PHPExcel/Writer/Excel2007.php';
         
        $xls = new PHPExcel();
        
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle('Лист1');
        
        
        $sheet->setCellValue("A3", "Дата");
        $sheet->setCellValue("B3", "Клиент");
        $sheet->setCellValue("C3", "Время прибытия\r\n (план/факт)");
        $sheet->setCellValue("E3", "Время отправления (план/факт)");
        $sheet->setCellValue("G3", "Водитель");
        $sheet->setCellValue("H3", "№ авто");
        $sheet->setCellValue("I3", "Причина");
        $sheet->setCellValue("J3", "Статус");
        
        $sheet->getColumnDimension("A")->setWidth(10);
        $sheet->getColumnDimension("B")->setWidth(21);
        $sheet->getColumnDimension("C")->setWidth(10);
        $sheet->getColumnDimension("D")->setWidth(10);
        $sheet->getColumnDimension("E")->setWidth(10);
        $sheet->getColumnDimension("F")->setWidth(10);
        $sheet->getColumnDimension("G")->setWidth(40);
        $sheet->getColumnDimension("H")->setWidth(30);
        $sheet->getColumnDimension("I")->setWidth(12);
        $sheet->getColumnDimension("J")->setWidth(8);
        
        $sheet->getStyle("A3:J3")->getAlignment()->setWrapText(true);
        $sheet->getStyle("A3:J3")->getFont()->setBold(true);
        $sheet->getStyle("A3:J3")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A3:J3")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension("3")->setRowHeight(30);

        $sheet->mergeCells("C3:D3");
        $sheet->mergeCells("E3:F3");
        $sheet->getStyle("A")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("J")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        $sheet->mergeCells("A1:J1");
        $sheet->setCellValue("A1", "Список отгрузок с ".date('d.m.Y', strtotime( $_POST['startdate']))." по ".date('d.m.Y', strtotime( $_POST['enddate'])));
        $sheet->getStyle("A1")->getFont()->setSize(18);
        
        
        
        //Подключение к базе
        $mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
        if ($mysqli->connect_error) {
            die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
        }
        
        $querystring = "SELECT shipping_clients.name, shipping_drivers.fio, shipping_auto.number, shipping_cause.cause AS causename, shipping_loading.*, TIME_TO_SEC(timediff(time_arrival_fact, time_arrival_plan)) AS diffarrival, TIME_TO_SEC(timediff(time_finish_fact, time_finish_plan)) AS difffinish FROM shipping_loading LEFT JOIN shipping_clients on shipping_loading.client = shipping_clients.id LEFT JOIN shipping_drivers ON shipping_loading.driver = shipping_drivers.id LEFT JOIN  shipping_cause ON shipping_loading.cause = shipping_cause.id LEFT JOIN shipping_auto ON shipping_loading.auto = shipping_auto.id WHERE shipping_loading.time_arrival_plan >= '".$_POST['startdate']." 00:00' AND shipping_loading.time_arrival_plan <= '".$_POST['enddate']." 23:59' ORDER BY shipping_loading.time_arrival_plan ASC";
  
    //echo $querystring;     
    $results = $mysqli->query($querystring);
    $line = 4;
    
    
        while($row = $results->fetch_assoc()) {
            
            //[name] => VW Калуга 
            //[fio] => Патрин МЕГА ТАРА 
            //[number] => МАН О911ВР/40 
            //[causename] => 
            //[id] => 1516 
            //[client] => 2 
            //[driver] => 251 
            //[auto] => 36 
            //[time_arrival_plan] => 2021-04-08 06:00:00 [time_arrival_fact] => 2021-04-07 19:11:00 
            //[time_finish_plan] => 2021-04-08 08:00:00 [time_finish_fact] => 2021-04-07 22:32:00 
            //[status] => 1 
            //[cause] => 
            //[diffarrival] => -38940 
            //[difffinish] => -34080 )
            
            //Дата	                Клиент	Время прибытия (план/факт)	Время отклонения (план/факт)	ВОДИТЕЛЬ	№ авто	    Причина 	Факт не соответствия
            //[time_arrival_plan]   [name]  [time_arrival_plan]         [time_finish_plan]              [fio]       [number]    [causename] [status]
            
            
            
            //date('H:i', strtotime($row['time_arrival_plan']));
            //echo date('d.m.Y', strtotime($row['time_arrival_plan']))."<br>";
            
            $sheet->setCellValue("A".$line, date('d.m.Y', strtotime($row['time_arrival_plan'])));
            $sheet->setCellValue("B".$line, $row['name']);
            $sheet->setCellValue("C".$line, date('H:i', strtotime($row['time_arrival_plan'])));
            $sheet->setCellValue("D".$line, date('H:i', strtotime($row['time_arrival_fact'])));
            $sheet->setCellValue("E".$line, date('H:i', strtotime($row['time_finish_plan'])));
            $sheet->setCellValue("F".$line, date('H:i', strtotime($row['time_finish_fact'])));
            
            if($row['diffarrival']>0) { $sheet->getStyle("D".$line)->getFont()->getColor()->setRGB('ff0000'); }
            if($row['difffinish']>0) { $sheet->getStyle("F".$line)->getFont()->getColor()->setRGB('ff0000'); }
            
            
            $sheet->setCellValue("G".$line, $row['fio']);
            $sheet->setCellValue("H".$line, $row['number']);
            $sheet->setCellValue("I".$line, $row['causename']);
            if($row['status'] == 1) $sheet->setCellValue("J".$line, "OK");
            if($row['status'] == 2) $sheet->setCellValue("J".$line, "?");
            if($row['status'] == 3) $sheet->setCellValue("J".$line, "NOK");
            $line++;
        }
        $border = array(
        	'borders'=>array(
        		'allborders' => array(
        			'style' => PHPExcel_Style_Border::BORDER_THIN,
        			'color' => array('rgb' => '000000')
        		)
        	)
        );
         
        $sheet->getStyle("A3:J".($line-1))->applyFromArray($border);

        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=BPR-Shipping.xlsx");
         
        $objWriter = new PHPExcel_Writer_Excel2007($xls);
        $objWriter->save('php://output'); 
        exit();

        
    }





    $inf .= "<div class='inputform'><form action='' method='post'>";
    if(!isset($_POST['showlist'])) {
        //$date1 = date('Y-m-d', time() - 86400);
        $date1 = date('Y-m-d');
        $date2 = date('Y-m-d');
    } else {
        $date1 = $_POST['startdate'];
        $date2 = $_POST['enddate'];
    }
    $inf .= "<input type='date' id='startdate' name='startdate' value='".$date1."'> - <input type='date' id='enddate' name='enddate' value='".$date2."'> <input type='button' name='today' id='today' value='Сегодня' /><br>";
    $inf .= " <input type='submit' name='exportxlsx' value='Выгрузить' />";
    $inf .= "</form></div>";
    return $inf;
}
// ========================= //



function checkdriver($clientid, $driver) {
    $mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }

    $querystring = "SELECT * FROM shipping_drivers WHERE `fio`='".$driver."'";
    $results = $mysqli->query($querystring);
    $count =  $results->num_rows;
    if($count > 0) { 
        $row = $results->fetch_assoc();
        $driverid = $row['id'];
    } else {
        $query = "INSERT INTO shipping_drivers (client, fio) VALUES (?, ?)";
        $statement = $mysqli->prepare($query);
        //s = string, i = integer, d = double,  b = blob
        $statement->bind_param('is', $clientid, $_POST['driver']);
        if($statement->execute()){
            $driverid = $statement->insert_id;
        }else{
        die('MESSAGE Error : ('. $mysqli->errno .') '. $mysqli->error);
        }
        $statement->close();
    }
    return $driverid;
}


function checkclient($client) {
    $mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
    $querystring = "SELECT * FROM shipping_clients WHERE `name`='".$client."'";
    $results = $mysqli->query($querystring);
    $count =  $results->num_rows;
    if($count > 0) { 
        $row = $results->fetch_assoc();
        $clientid = $row['id'];
    } else { 
        $query = "INSERT INTO shipping_clients (name) VALUES (?)";
        $statement = $mysqli->prepare($query);
        //s = string, i = integer, d = double,  b = blob
        $statement->bind_param('s', $client);
        if($statement->execute()){
             $clientid = $statement->insert_id;
        }else{
        die('MESSAGE Error : ('. $mysqli->errno .') '. $mysqli->error);
        }
        $statement->close();
    }
    return $clientid;
}

function checkcause($cause) {
    $mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
    $querystring = "SELECT * FROM shipping_cause WHERE `cause`='".$cause."'";
    $results = $mysqli->query($querystring);
    $count =  $results->num_rows;
    if($count > 0) { 
        $row = $results->fetch_assoc();
        $causeid = $row['id'];
    } else { 
        $query = "INSERT INTO shipping_cause (cause) VALUES (?)";
        $statement = $mysqli->prepare($query);
        //s = string, i = integer, d = double,  b = blob
        $statement->bind_param('s', $cause);
        if($statement->execute()){
             $causeid = $statement->insert_id;
        }else{
        die('MESSAGE Error : ('. $mysqli->errno .') '. $mysqli->error);
        }
        $statement->close();
    }
    return $causeid;
}

function checkauto($numberauto) {
    $mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
    $querystring = "SELECT * FROM shipping_auto WHERE `number`='".$numberauto."'";
    $results = $mysqli->query($querystring);
    $count =  $results->num_rows;
    if($count > 0) { 
        $row = $results->fetch_assoc();
        $autoid = $row['id'];
    } else { 
        $query = "INSERT INTO shipping_auto (number) VALUES (?)";
        $statement = $mysqli->prepare($query);
        //s = string, i = integer, d = double,  b = blob
        $statement->bind_param('s', $numberauto);
        if($statement->execute()){
             $autoid = $statement->insert_id;
        }else{
        die('MESSAGE Error : ('. $mysqli->errno .') '. $mysqli->error);
        }
        $statement->close();
    }
    return $autoid;
}

?>