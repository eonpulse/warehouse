<style>
    .man{
        padding: 30px 0px 0px 20px;
    } 
    @media print {
    .man {
        display: none !important;
    }   
</style>
<div class="inputform"><form action="" method="post" enctype="multipart/form-data">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="file" name="csv" value="" />
<input type="submit" name="submit" value="Сделать хорошо" /></form></div>
<?php
$this->inf = '';
mb_internal_encoding("UTF-8");

$mysqli = new mysqli('localhost', 'warehouse', 'warehouse@2018', 'warehouse');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}





// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] == "POST" ) {
	
	
	$csv = array();
	// check there are no errors
	if($_FILES['csv']['error'] == 0){
		$name = $_FILES['csv']['name'];
		$ext = strtolower(end(explode('.', $_FILES['csv']['name'])));
		$type = $_FILES['csv']['type'];
		$tmpName = $_FILES['csv']['tmp_name'];

		// check the file is a csv
		if($ext === 'csv'){
			if(($handle = fopen($tmpName, 'r')) !== FALSE) {
				// necessary if a large csv file
				set_time_limit(0);

				//$row = 0;

				while(($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
					// number of fields in the csv
					$col_count = count($data);

					// get the values from the csv
					$product = $data[1];
					$lot = str_pad($data[4], 7, '0', STR_PAD_LEFT);
					$lot = $data[4];
					$box = str_pad($data[13], 3, '0', STR_PAD_LEFT);
					$quantity = str_replace('.',',',$data[3]);
					$terminal = $data[12];
					$tabel = $data[14];
					//$csv[$row]['col1'] = $data[0];
					//$csv[$row]['col2'] = $data[1];

					// inc the row
					//$row++;
					echo $product." - ".$lot." - ".$box." - ".$quantity." - ".$terminal." - ".$tabel."<br>";
					autoadd($product, $lot, $box, $quantity, $terminal, $tabel);
				}
				fclose($handle);
			}
		}
	}
	







}


function autoadd($product, $lot, $box, $quantity, $operator, $tabel) {
	echo $quantity;
	//$quantity = $quantity.",000";
	
	$soap_request  = "\357\273\277";
	$soap_request .= "<s:Envelope xmlns:s=\"http://schemas.xmlsoap.org/soap/envelope/\">";
	$soap_request .= "<s:Body>";
	$soap_request .= "  <SetInformation";
	$soap_request .= "    xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n";
	$soap_request .= "    xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\"\n";
	$soap_request .= "    xmlns=\"http://tempuri.org/\"\n>";
	$soap_request .= "<information>";
	$soap_request .= "	<BatchNumber xmlns=\"http://schemas.datacontract.org/2004/07/Infocom.BarcodeSystem.DataModel.ViewModel\">".$lot."</BatchNumber>";
	$soap_request .= "	<BoxNumber xmlns=\"http://schemas.datacontract.org/2004/07/Infocom.BarcodeSystem.DataModel.ViewModel\">".$box."</BoxNumber>";
	$soap_request .= "	<ProductNumber xmlns=\"http://schemas.datacontract.org/2004/07/Infocom.BarcodeSystem.DataModel.ViewModel\">".$product."</ProductNumber>";
	$soap_request .= "	<Quantity xmlns=\"http://schemas.datacontract.org/2004/07/Infocom.BarcodeSystem.DataModel.ViewModel\">".$quantity."</Quantity>";
	$soap_request .= "	<TerminalNumber xmlns=\"http://schemas.datacontract.org/2004/07/Infocom.BarcodeSystem.DataModel.ViewModel\">".$operator."</TerminalNumber>";
	$soap_request .= "	<UserId xmlns=\"http://schemas.datacontract.org/2004/07/Infocom.BarcodeSystem.DataModel.ViewModel\">".$tabel."</UserId>";
	$soap_request .= "</information>";
	$soap_request .= "</SetInformation>";
	$soap_request .= "</s:Body>";
	$soap_request .= "</s:Envelope>";

//echo $soap_request."<br>";
//echo $product."-".$lot."-".$box."-".$quantity."<br>";
	
	$header = array(
		"Content-type: text/xml;charset=\"utf-8\"",
		"Accept: text/xml",
		"Cache-Control: no-cache",
		"Pragma: no-cache",
		"SOAPAction: http://tempuri.org/IISHttpService/SetInformation",
		"Content-length: ".strlen($soap_request),
	);

    $soap_do = curl_init();
    curl_setopt($soap_do, CURLOPT_URL, "http://10.20.2.21:3334" );
    curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
    curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($soap_do, CURLOPT_POST,           true );
    curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $soap_request);
    curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
    $req = curl_exec($soap_do);
    if($req  === false) {
		$err = 'Curl error: ' . curl_error($soap_do);
		curl_close($soap_do);
		print $err;
    } else {
		print_r($req);
		curl_close($soap_do);
		//print 'Operation completed without any errors';
	}
	
}


?>


<?php

class barcode {

  protected static $code39 = array(
    '0' => 'bwbwwwbbbwbbbwbw', '1' => 'bbbwbwwwbwbwbbbw',
    '2' => 'bwbbbwwwbwbwbbbw', '3' => 'bbbwbbbwwwbwbwbw',
    '4' => 'bwbwwwbbbwbwbbbw', '5' => 'bbbwbwwwbbbwbwbw',
    '6' => 'bwbbbwwwbbbwbwbw', '7' => 'bwbwwwbwbbbwbbbw',
    '8' => 'bbbwbwwwbwbbbwbw', '9' => 'bwbbbwwwbwbbbwbw',
    'A' => 'bbbwbwbwwwbwbbbw', 'B' => 'bwbbbwbwwwbwbbbw',
    'C' => 'bbbwbbbwbwwwbwbw', 'D' => 'bwbwbbbwwwbwbbbw',
    'E' => 'bbbwbwbbbwwwbwbw', 'F' => 'bwbbbwbbbwwwbwbw',
    'G' => 'bwbwbwwwbbbwbbbw', 'H' => 'bbbwbwbwwwbbbwbw',
    'I' => 'bwbbbwbwwwbbbwbw', 'J' => 'bwbwbbbwwwbbbwbw',
    'K' => 'bbbwbwbwbwwwbbbw', 'L' => 'bwbbbwbwbwwwbbbw',
    'M' => 'bbbwbbbwbwbwwwbw', 'N' => 'bwbwbbbwbwwwbbbw',
    'O' => 'bbbwbwbbbwbwwwbw', 'P' => 'bwbbbwbbbwbwwwbw',
    'Q' => 'bwbwbwbbbwwwbbbw', 'R' => 'bbbwbwbwbbbwwwbw',
    'S' => 'bwbbbwbwbbbwwwbw', 'T' => 'bwbwbbbwbbbwwwbw',
    'U' => 'bbbwwwbwbwbwbbbw', 'V' => 'bwwwbbbwbwbwbbbw',
    'W' => 'bbbwwwbbbwbwbwbw', 'X' => 'bwwwbwbbbwbwbbbw',
    'Y' => 'bbbwwwbwbbbwbwbw', 'Z' => 'bwwwbbbwbbbwbwbw',
    '-' => 'bwwwbwbwbbbwbbbw', '.' => 'bbbwwwbwbwbbbwbw',
    ' ' => 'bwwwbbbwbwbbbwbw', '*' => 'bwwwbwbbbwbbbwbw',
    '$' => 'bwwwbwwwbwwwbwbw', '/' => 'bwwwbwwwbwbwwwbw',
    '+' => 'bwwwbwbwwwbwwwbw', '%' => 'bwbwwwbwwwbwwwbw'
  );

/*  
    protected static $code128 = array(
    '0' => 'bwwbbbwbbww', '1' => 'bwwbbbwwbbw',
    '2' => 'bbwwbbbwwbw', '3' => 'bbwwbwbbbww',
    '4' => 'bbwwbwwbbbw', '5' => 'bbwbbbwwbww',
    '6' => 'bbwwbbbwbww', '7' => 'bbbwbbwbbbw',
    '8' => 'bbbwbwwbbww', '9' => 'bbbwwbwbbww'
  );
  
      protected static $code128id = array(
    '0' => 16, '1' => 17,
    '2' => 18', '3' => 19,
    '4' => 20, '5' => 21,
    '6' => 22, '7' => 23',
    '8' => 24, '9' => 25
  );
  
  protected static $code128start = 'bbwbwwwwbww';
  protected static $code128stop = 'bbwwwbbbwbwbb';
  */

  public static function code39($text) {
    if (!preg_match('/^[A-Z0-9-. $+\/%]+$/i', $text)) {
      throw new Exception('Ошибка ввода');
    }

    $text = '*'.strtoupper($text).'*';
    $length = strlen($text);
    $chars = str_split($text);
    $colors = '';

    foreach ($chars as $char) {
      $colors .= self::$code39[$char];
    }

    $html = '
            <div style=" float:left;">
            <div>';

    foreach (str_split($colors) as $i => $color) {
      if ($color=='b') {
        $html.='<SPAN style="BORDER-LEFT: 0.01in solid; DISPLAY: inline-block; HEIGHT: 0.7in;"></SPAN>';
      } else {
        $html.='<SPAN style="BORDER-LEFT: white 0.01in solid; DISPLAY: inline-block; HEIGHT: 0.7in;"></SPAN>';
      }
    }

    $html.='</div>
            <div style="float:left; width:100%;" align=center >'.$text.'</div></div>';
    //echo htmlspecialchars($html);
    return $html;
  }

/*
  public static function code128($text) {
    if (!preg_match('/^[0-9-. $+\/%]+$/i', $text)) {
      throw new Exception('Ошибка ввода');
    }

    //$text = '*'.strtoupper($text).'*';
    $length = strlen($text);
    $chars = str_split($text);
    $colors = '';
    $summ = 103;
    $i = 1;
    foreach ($chars as $char) {
      $colors .= self::$code128[$char];
      $summ = $summ+(self::$code128id[$char] * $i);
      $i++;
    }
    $control = $summ % 103;

    $html = '
            <div style=" float:left;">
            <div>';

    foreach (str_split($colors) as $i => $color) {
      if ($color=='b') {
        $html.='<SPAN style="BORDER-LEFT: 0.01in solid; DISPLAY: inline-block; HEIGHT: 0.7in;"></SPAN>';
      } else {
        $html.='<SPAN style="BORDER-LEFT: white 0.01in solid; DISPLAY: inline-block; HEIGHT: 0.7in;"></SPAN>';
      }
    }

    $html.='</div>
            <div style="float:left; width:100%;" align=center >'.$text.'</div></div>';
    //echo htmlspecialchars($html);
    return $html;
  }
*/


}

class Barcode128 {
    static private $encoding = array(
        '11011001100', '11001101100', '11001100110', '10010011000',
        '10010001100', '10001001100', '10011001000', '10011000100',
        '10001100100', '11001001000', '11001000100', '11000100100',
        '10110011100', '10011011100', '10011001110', '10111001100',
        '10011101100', '10011100110', '11001110010', '11001011100',
        '11001001110', '11011100100', '11001110100', '11101101110',
        '11101001100', '11100101100', '11100100110', '11101100100',
        '11100110100', '11100110010', '11011011000', '11011000110',
        '11000110110', '10100011000', '10001011000', '10001000110',
        '10110001000', '10001101000', '10001100010', '11010001000',
        '11000101000', '11000100010', '10110111000', '10110001110',
        '10001101110', '10111011000', '10111000110', '10001110110',
        '11101110110', '11010001110', '11000101110', '11011101000',
        '11011100010', '11011101110', '11101011000', '11101000110',
        '11100010110', '11101101000', '11101100010', '11100011010',
        '11101111010', '11001000010', '11110001010', '10100110000',
        '10100001100', '10010110000', '10010000110', '10000101100',
        '10000100110', '10110010000', '10110000100', '10011010000',
        '10011000010', '10000110100', '10000110010', '11000010010',
        '11001010000', '11110111010', '11000010100', '10001111010',
        '10100111100', '10010111100', '10010011110', '10111100100',
        '10011110100', '10011110010', '11110100100', '11110010100',
        '11110010010', '11011011110', '11011110110', '11110110110',
        '10101111000', '10100011110', '10001011110', '10111101000',
        '10111100010', '11110101000', '11110100010', '10111011110',
        '10111101110', '11101011110', '11110101110', '11010000100',
        '11010010000', '11010011100', '11000111010');
    static public function getDigit($code){
        $tableB = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~";
        $result = "";
        $sum = 0;
        $isum = 0;
        $i = 0;
        $j = 0;
        $value = 0;

        // check each characters
        $len = strlen($code);
        for($i=0; $i<$len; $i++){
            if (strpos($tableB, $code[$i]) === false) return("");
        }

        // check firsts characters : start with C table only if enought numeric
        $tableCActivated = $len> 1;
        $c = '';
        for($i=0; $i<3 && $i<$len; $i++){
            $tableCActivated &= preg_match('`[0-9]`', $code[$i]);
        }

        $sum = $tableCActivated ? 105 : 104;

        // start : [105] : C table or [104] : B table
        $result = self::$encoding[ $sum ];

        $i = 0;
        while( $i < $len ){
            if (! $tableCActivated){
                $j = 0;
                // check next character to activate C table if interresting
                while ( ($i + $j < $len) && preg_match('`[0-9]`', $code[$i+$j]) ) $j++;

                // 6 min everywhere or 4 mini at the end
                $tableCActivated = ($j > 5) || (($i + $j - 1 == $len) && ($j > 3));

                if ( $tableCActivated ){
                    $result .= self::$encoding[ 99 ]; // C table
                    $sum += ++$isum * 99;
                }
                // 2 min for table C so need table B
            } else if ( ($i == $len - 1) || (preg_match('`[^0-9]`', $code[$i])) || (preg_match('`[^0-9]`', $code[$i+1])) ) { //todo : verifier le JS : len - 1!!! XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
                $tableCActivated = false;
                $result .= self::$encoding[ 100 ]; // B table
                $sum += ++$isum * 100;
            }

            if ( $tableCActivated ) {
                $value = intval(substr($code, $i, 2)); // Add two characters (numeric)
                $i += 2;
            } else {
                $value = strpos($tableB, $code[$i]); // Add one character
                $i++;
            }
            $result  .= self::$encoding[ $value ];
            $sum += ++$isum * $value;
        }

        // Add CRC
        $result  .= self::$encoding[ $sum % 103 ];

        // Stop
        $result .= self::$encoding[ 106 ];

        // Termination bar
        $result .= '11';

        return($result);
    }
    static public function getCode($code){
        $code = self::getDigit($code);
        foreach (str_split($code) as $i => $color) {
            if ($color=='1') {
                //$html.='<SPAN style="BORDER-LEFT: black 0.02in solid; DISPLAY: inline-block; HEIGHT: 0.7in; background-color: black;"></SPAN>';
                $html.='<SPAN style="DISPLAY: inline-block; HEIGHT: 0.7in; width: 2px; background-color: black;"></SPAN>';
            } else {
                //$html.='<SPAN style="BORDER-LEFT: white 0.02in solid; DISPLAY: inline-block; HEIGHT: 0.7in; background-color: white;"></SPAN>';
                $html.='<SPAN style="DISPLAY: inline-block; HEIGHT: 0.7in; width: 2px; background-color: white;"></SPAN>';
            }
        }
        return($html);
    }
    
}
