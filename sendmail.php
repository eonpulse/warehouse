<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';


echo "Test mail";
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
        $mail->msgHTML("Тест. К производственному наряду открыто подтверждение о изготовлениии");
        // Отправляем
        
        if ($mail->send()) {
          echo 'Письмо отправлено!';
        } else {
          //echo 'Ошибка: ' . $mail->ErrorInfo;
        }
        
    }

?>