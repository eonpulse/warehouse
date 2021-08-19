<?php
class err
{
	function err($url)
	{
		$this->errorcode = $url[2];
	}
	

	
	function get_error()
	{
		switch($this->errorcode){
			case '404':
				return "<h1>Error 404</h1><h2>Нет такой страницы</h2><img src='/images/box.jpg'>";
				break;
			case '505':
				return "login incorrect";
				break;
		}
	}

}
?>