<?php
class kernel
{
    function kernel()
    {
        
        $this->resurs_mysql = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_BASENAME);
        if ($this->resurs_mysql->connect_error) {
            die('Connect Error (' . $this->resurs_mysql->connect_errno . ') ' . $this->resurs_mysql->connect_error);
        }
    }
	
	//Разбор URL
	function parser_url()
	{
		$url = explode("/", htmlspecialchars(stripslashes($_SERVER[REQUEST_URI])));
		$i=0;
		while(isset($url[$i]))
		{
			$i++;
		}
		if ($url[1]=="" ||
			$url[1]=="index.php" ||
			$url[1]=="index.html")
				$url[1]=DEFAULT_PAGE;
		return $url;
	}

}
?>