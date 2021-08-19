<?php
include PATH_PAGE_CONTENT."/err/err.class.php";




$err = new err($this->url);
$this->inf = $err->get_error();

//$this->inf = $error->errorcode;


?>