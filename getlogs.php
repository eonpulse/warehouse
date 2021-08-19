<?php
// Подключаем библиотеку PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';


include('ini.php');
$inf = '';
$errlist = '';


mb_internal_encoding("UTF-8");

function manualparse($words, $date) {
    $operator = trim($words[0]);
    $product = (int)trim($words[1]);
    $a = explode(" ", trim($words[4]));
    //echo $a[0];
    $cause = trim(str_replace("Причина:", "", $words[5]));
    if($a[0] == "Перечисление") {
        $action = 1;
        for ($i = 6; $i < count($words); $i++){
            $str = preg_replace('/[\s]{2,}/', ' ', trim($words[$i]));
            $line = explode(" ", $str);
            if(count($line)>5) {
                $quantity1 = (float)$line[0];
                $quantity2 = (float)$line[0];
                $warehouse1 = $line[6];
                $warehouse2 = $line[9];
                $lot = (int)trim($line[2], ",");
                $lottest = (int)substr($line[3], 0, -4);
                $box = (int)substr($line[3], -3);
                $lot2 = (int)trim($line[10], ",");
                $lottest2 = (int)substr($line[11], 0, -4);
                $box2 = (int)substr($line[11], -3);
                $cause1 = $cause;
                if($lot != $lot2) { $cause1 .= " [!=Номер партии изменён ".$lot." &#10144; ".$lot2."=!]"; }
                if($box != $box2) { $cause1 .= " [!=Номер коробки изменён ".$line[3]." &#10144; ".$line[11]."=!]"; }
                if($lot2 != $lottest2) { $cause1 .= " [!=Применение не соответствует партии ".$lot2." &#10144; ".$line[3]."=!]"; }
                //print_r($line);
                //echo $words[$i]." = ".$quantity1." ".$quantity2." [". $lot."] ".$lottest." ".$box." [". $lot2."] ".$lottest2." ".$box2." ".$warehouse1."-".$warehouse2."<br>";
                $cause1 = substr($cause1, 0, 200);
                manualsql($date, $action, $operator, $product, $lot, $box, $quantity1, $quantity2, $warehouse1, $warehouse2, $cause1);
            }
        }
    } else if($a[0] == "Поступление") {
        $action = 2;
        for ($i = 6; $i < count($words); $i++){
            $str = preg_replace('/[\s]{2,}/', ' ', trim($words[$i]));
            $line = explode(" ", $str);
            if(count($line)>5) {
                $quantity1 = 0;
                $quantity2 = (float)$line[0];
                $warehouse1 = "";
                $warehouse2 = $line[8];
                $lot = (int)$line[3];
                $lottest = (int)substr($line[5], 0, -4);
                $box = (int)substr($line[5], -3);
                $cause1 = $cause;
                if($lot == 0) { $cause1 .= " [!=Пустой номер партии=!]"; }
                else if($lot != $lottest) { $cause1 .= " [!=Применение не соответствует партии ".$lot." &#10144; ".$line[5]."=!]"; }
                //print_r($line);
                //echo $words[$i]." = ".$quantity1." ".$quantity2." [". $lot."] ".$lottest." ".$box." ".$warehouse1."-".$warehouse2."<br>";
                $cause1 = substr($cause1, 0, 200);
                manualsql($date, $action, $operator, $product, $lot, $box, $quantity1, $quantity2, $warehouse1, $warehouse2, $cause1);
            }
        }
    } else if($a[0] == "Выбытие") {
        $action = 3;
        for ($i = 6; $i < count($words); $i++){
            $str = preg_replace('/[\s]{2,}/', ' ', trim($words[$i]));
            $line = explode(" ", $str);
            if(count($line)>5) {
                $quantity1 = (float)$line[0];
                $quantity2 = 0;
                $warehouse1 = $line[8];
                $warehouse2 = "";
                $lot = (int)trim($line[3], ",");
                $lottest = (int)substr($line[5], 0, -4);
                $box = (int)substr($line[5], -3);
                $cause1 = $cause;
                if($lot == 0) { $cause1 .= " [!=Пустой номер партии=!]"; }
                else if($lot != $lottest) { $cause1 .= " [!=Применение не соответствует партии ".$lot." &#10144; ".$line[5]."=!]"; }
                //print_r($line);
                //echo $words[$i]." = ".$quantity1." ".$quantity2." [". $lot."] ".$lottest." ".$box."<br>";
                $cause1 = substr($cause1, 0, 200);
                manualsql($date, $action, $operator, $product, $lot, $box, $quantity1, $quantity2, $warehouse1, $warehouse2, $cause1);
            }
            
        }
    } else if($a[0] == "Исправление") {
        $action = 4;
        $warehouse1 = $a[4];
        $warehouse2 = $a[4];
        for ($i = 6; $i < count($words); $i++){
            $str = preg_replace('/[\s]{2,}/', ' ', trim($words[$i]));
            $line = explode(" ", $str);
            if(count($line)>5) {
                $str = preg_replace('/\s+/', ' ', trim($words[$i]));
                $line = explode(" ", $str);
                $quantity1 = (float)$line[2];
                $quantity2 = (float)$line[5];
                $lot = (int)trim($line[8], ",");
                $lottest = (int)substr($line[10], 0, -4);
                $box = (int)substr($line[10], -3);
                $cause1 = $cause;
                if($lot == 0) { $cause1 .= " [!=Пустой номер партии=!]"; }
                else if($lot != $lottest) { $cause1 .= " [!=Применение не соответствует партии ".$lot." &#10144; ".$line[12]."=!]"; }
                //print_r($line);
                //echo $words[$i]." = ".$quantity1." ".$quantity2." [". $lot."] ".$lottest." ".$box." ".$cause."<br>";
                $cause1 = substr($cause1, 0, 200);
                if($quantity1 != $quantity2) { manualsql($date, $action, $operator, $product, $lot, $box, $quantity1, $quantity2, $warehouse1, $warehouse2, $cause1); }
            }
        }
    }       
}



function manualsql($date, $action, $operator, $product, $lot, $box, $quantity1, $quantity2, $warehouse1, $warehouse2, $cause) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
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
}


$mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}



    $dir = PATH_FILES."/terminal/doku/";
    $files = scandir($dir);
    $countfiles=0;
    foreach($files as $file) {
            $path_info = pathinfo($file);
            if($path_info['extension'] == 'TMP') {
                $err = 0;
                //$inf .=  '<div class="link typevideo" onclick=\'return ChangeFile("'.$_SESSION['detail_number'].'", "'.$file.'", "'.$path_info['extension'].'")\' href="'.$file.'">'.$file.'</div>';
                $str = htmlentities(file_get_contents($dir.$file));
                $words = explode("\n", $str);
                //echo "[".$words[1]."]<br>";
                //$data = date('d.m.Y H:i:s', filemtime($dir.$file));
                $date = filemtime($dir.$file);
                //$inf .= $str."<br>";
                //echo(substr(preg_replace('/\d|\s/', '', $words[3]),0,66));
                //===== [1] ОК =====
                if(preg_replace('/\d|\s/', '', $words[3]) == "Изделиепартии,коробкаоприходовановколичестве"){
					$status = "1";
					$t = explode(" ", trim($words[3]));
                    $terminal = trim($words[0]);
                    $product = (int)trim($t[1], ",");
                    $lot = (int)trim($t[3], ",");
                    $box = (int)trim($t[5], ",");
                    $quantity = (float)trim($t[9], ",");
                    $note = $words[1];
					//$inf .= "<p style='color: #00bb00;'>";
                    //$inf .=  $date." ".$status." ".$terminal." ".$product." ".$lot." ".$quantity;
					//$inf .= " - Успешно оприходовано (наряд ".$note.")</p>";	
                
                //===== [2] ПОВТОРНО =====
                } else if(trim($words[1]) == "Ошибка повторного оприходования") {
                    $status = "2";
                    $t = explode(" ", trim($words[2]));
                    $terminal = trim($words[0]);
                    $product = (int)$t[1];
                    $lot = (int)trim($t[5], ",");
                    $box = trim($t[8], ",");
                    $quantity = (float)$t[10];
                    $note = trim($t[8], ",");
					//$inf .= "<p style='color: #999999;'>";
                    //$inf .=  $date." ".$status." ".$terminal." ".$product." ".$lot." ".$quantity;
					//$inf .= " - Повторно, коробка № ".$note."</p>";
				
				//===== [3] НЕТ МАТЕРИАЛА =====	
                } else if(trim($words[5]) == "Списание материалов невозможно!") {
					$status = "3";
					$t = explode(" ", trim($words[3]));
                    $terminal = trim($words[0]);
                    $product = (int)trim($t[1], ",");
                    $lot = (int)trim($t[3], ",");
                    $box = (int)trim($t[5], ",");
					$t1 = explode(" ", trim($words[6]));
					//$note = $t1[3];
					$note = str_replace("Недостаточно следующих материалов: ", "", trim($words[6]));
					$t2 = explode(" ", trim($words[4]));
					$quantity = (float)trim($t2[5], ",");
					//$inf .= "<p style='color: #bb0000;'>";
                    //$inf .=  $date." ".$status." ".$terminal." ".$product." ".$lot." ".$quantity;
                    //$inf .= " - Не хватает материала ".$note;
					//$inf .= "</p>";	

				//===== [4] НЕТ НАРЯДА =====	
                } else if(trim($words[1]) == "В системе не найдено ни одного производственного наряда! Оприходование продукции невозможно.") {
                    $status = "4";
                    $t = explode(" ", trim($words[2]));
                    $terminal = trim($words[0]);
                    $product = (int)trim($t[2], "{},");
                    $lot = (int)trim($t[6], ",");
                    $note = "";
                    $quantity = 0;
                    $box = trim($t[4], ",");
					//$inf .= "<p style='color: #0000bb;'>";
                    //$inf .=  $date." ".$status." ".$terminal." ".$product." ".$lot." ".$quantity;
					//$inf .= " - Нет наряда</p>";
				
				//===== [5] ЗАБЛОКИРОВАН НАРЯД =====
				} else if(preg_replace('/\d|\s/', '', $words[1]) == "Кпроизводственномунаряду№открытоподтверждениеоизготовлениии.Пожалуйстаустранитепричинублокировкиипопробуйтеснова.") {
					$status = "5";
					$t = explode(" ", trim($words[1]));
                    $terminal = trim($words[0]);
                    $product = 0;
                    $lot = 0;
                    $note = trim($t[4], ",");
                    $quantity = 0;
                    $box = 0;
					//$inf .= "<p style='color: #bb0000;'>";
                    //$inf .=  $date." ".$status." ".$terminal." ".$product." ".$lot." ".$quantity;
					//$inf .= " - Заблокирован наряд</p>";	
					
					
					
					
					$maillist = array('aleksander.nikulin@bap.boryszew.ru', 'aleksander.osenchugov@bap.boryszew.ru');

                    foreach ($maillist as $mailto) {
                        // Создаем письмо
                        $mail = new PHPMailer();
                        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;
                        $mail->CharSet = 'UTF-8';
                        $mail->isSMTP();                   // Отправка через SMTP
                        $mail->Host   = '10.20.2.3';  // Адрес SMTP сервера
                        $mail->SMTPAuth   = false;          // Enable SMTP authentication
                        $mail->Username   = 'bprv-app-07@bap.boryszew.ru';       // ваше имя пользователя
                        $mail->Password   = '';    // ваш пароль
                        //$mail->SMTPSecure = 'tls';         // шифрование ssl
                        $mail->SMTPSecure = false;
                        $mail->SMTPAutoTLS = false;
                        $mail->Port   = 587;               // порт подключения
                        $mail->setFrom('bprv-app-07@bap.boryszew.ru', 'WAREHOUSE');    // от кого
                        $mail->addAddress($mailto); // кому
                        $mail->Subject = "Открыто подтверждение о изготовлениии";
                        $mail->msgHTML("К производственному наряду ".$note." открыто подтверждение о изготовлениии");
                        // Отправляем
                        
                        if ($mail->send()) {
                          //echo 'Письмо отправлено!';
                        } else {
                          //echo 'Ошибка: ' . $mail->ErrorInfo;
                        }
                        
                    }
					
					
					
					
					
				} else if(preg_replace('/\d|\s/', '', $words[1]) == "Оприходованиенеудалось,т.к.производственныйнарядзанят.Пожалуйстаустранитепричинублокировкиипопробуйтеснова.") {
					$status = "5";
					$t = explode(" ", trim($words[1]));
                    $terminal = trim($words[0]);
                    $product = 0;
                    $lot = 0;
                    $note = trim($t[6], ",");
                    $quantity = 0;
                    $box = 0;
					//$inf .= "<p style='color: #bb0000;'>";
                    //$inf .=  $date." ".$status." ".$terminal." ".$product." ".$lot." ".$quantity;
					//$inf .= " - Заблокирован наряд</p>";		
				
				//===== [6] СПИСАН БРАК =====
				} else if(substr($words[3], 0, 12) == "Списан") {
				    $status = "6";
					$t = explode(" ", trim($words[3]));
                    $terminal = trim($words[0]);
                    $product = trim($t[4], ",");
                    $lot = 0;
                    $box = 0;
                    $quantity = (float)trim($t[9], ",");
					//$inf .= "<p style='color: #000000;'>";
                    //$inf .=  $date." ".$status." ".$terminal." ".$product." ".$lot." ".$quantity;
					//$inf .= " - Списан брак</p>";
				
				//===== [MAN] ЛОГ РУЧНЫХ ИЗМЕНЕНИЙ =====
				} else if(substr(preg_replace('/\d|\s/', '', $words[3]),0,66) == "Сизделиембылипроизведеныследующие") {
				    $status = "999";
				    $err = 999;
				    manualparse($words, $date);
				    rename($dir.$file, $dir."man/".$file);
				} else {
				    $err = 1;
				}
	
                //===== ДОБАВЛЯЕМ В БД =====
                //echo $str."<br>";
            
            if($err == 0) {   
                $query = "INSERT INTO messages (date, status, terminal, product, lot, quantity, note, box) VALUES( ?, ?, ?, ?, ?, ?, ?, ?)";
                $statement = $mysqli->prepare($query);
                //s = string, i = integer, d = double,  b = blob
                $statement->bind_param('iisiidsi', $date, $status, $terminal, $product, $lot, $quantity, $note, $box);
                if($statement->execute()){
                    //print 'Success! ID of last inserted record is : ' .$statement->insert_id .'<br />';
  
                }else{
                    echo $str."<br>";
                    die('MESSAGE Error : ('. $mysqli->errno .') '. $mysqli->error);
                }
                $statement->close();
                
                
                
                // ===== Все кроме списания брака  =====
                if($status != "6") {
                    $querystring = "SELECT status, note FROM alerts WHERE product = ".$product." AND lot = ".$lot." AND box = ".$box." LIMIT 1";
                    $results = $mysqli->query($querystring);
                    // ===== Уже есть в базе ======
                    if($results->num_rows > 0) {
                        $row = $results->fetch_assoc();
                        // ===== Если статус изменился ======
                        if($row['status'] != $status OR $row['note'] != $note) {
                            // ===== Если статус УСПЕШНО - удаляем ошибку, Иначе меняем статус =====
                            if((int)$status < 3) {
                                $query = "DELETE FROM alerts WHERE product=? AND lot=? AND box=?";
                                $statement = $mysqli->prepare($query);
                                $statement->bind_param('iii', $product, $lot, $box);
                                if($statement->execute()){
                                    //print 'Success! ID of last inserted record is : ' .$statement->insert_id .'<br />';
                                }else{
                                    echo $str."<br>";
                                    die('ALARM UPDATE Error : ('. $mysqli->errno .') '. $mysqli->error);
                                }
                                $statement->close();
                            } else {
                                if($quantity>0) {
                                    $query = "UPDATE alerts SET date=?, status=?, terminal=?, quantity=?, note=? WHERE product=? AND lot=? AND box=?";
                                    $statement = $mysqli->prepare($query);
                                    $statement->bind_param('iiiisiii', $date, $status, $terminal, $quantity, $note, $product, $lot, $box);
                                } else {
                                    $query = "UPDATE alerts SET date=?, status=?, terminal=?, note=? WHERE product=? AND lot=? AND box=?";
                                    $statement = $mysqli->prepare($query);
                                    $statement->bind_param('iiisiii', $date, $status, $terminal, $note, $product, $lot, $box);
                                }
                                if($statement->execute()){
                                    //print 'Success! ID of last inserted record is : ' .$statement->insert_id .'<br />';
                                }else{
                                    echo $str."<br>";
                                    die('ALARM UPDATE Error : ('. $mysqli->errno .') '. $mysqli->error);
                                }
                                $statement->close();
                            }
                        }
                    } else if($status != "1" AND $status != "2") {
                        // ===== Если новая ошибка - добавляем =====
                        $query = "INSERT INTO alerts (date, status, terminal, product, lot, box, quantity, note) VALUES( ?, ?, ?, ?, ?, ?, ?, ?)";
                        $statement = $mysqli->prepare($query);
                        $statement->bind_param('iisiiids', $date, $status, $terminal, $product, $lot, $box, $quantity, $note);
                        if($statement->execute()){
                            //print 'Success! ID of last inserted record is : ' .$statement->insert_id .'<br />';
                        }else{
                            echo $str."<br>";
                            die('ALARM ADD Error : ('. $mysqli->errno .') '. $mysqli->error);
                        }
                        $statement->close();
                    } else {
                    }
                }
                //unlink($dir.$file);
                $path = $dir."history/".date('Y_m', time());
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                rename($dir.$file, $path."/".$file);
                
                $countfiles++;
                //if($countfiles>1) break;
            } else if($err == 1) { 
               //echo "Ошибка добавления: ".$str."<br>";
               $errlist .= "<div>".$str."</div>";
            }
        
        
        }    
    }
    //if($countfiles == 0) $inf .= '[%FilesMissing%]';









//$path = '\\192.168.0.10\Public\program\service_car\old_MonitorPr.txt';
//$content = file_get_contents($path);
//echo $content;


echo $inf;
if($errlist != '') {
echo "<div class='errbtn'></div><div class='errlist'>".$errlist."</div>";
}


?>