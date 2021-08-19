<?php
class display
{
	function display()
	{
		$this->lang = DEFAULT_LANGUAGE;
		$this->resurs_mysql = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_BASENAME);
        if ($this->resurs_mysql->connect_error) {
            die('Connect Error (' . $this->resurs_mysql->connect_errno . ') ' . $this->resurs_mysql->connect_error);
        }
	}

 	/* Замена [кода] на нужный язык
	** str $inf - текст в котором ищем
	*/	
	

	function parser_lang($inf)
	{
		$stmt = $this->resurs_mysql->prepare("SELECT string, ".$this->lang." FROM lang"); 
        $stmt->bind_param('s', $this->lang);
//        echo $this->lang;
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($string, $translate);
		while ($stmt->fetch()) {
//		echo $string." = ".$translate;
		    $inf = str_replace("[%".$string."%]",$translate,$inf);
        }
		return $inf;
	}

	


	function create_page($inf)
	{
		$base = file_get_contents(PATH_PAGE_TEMPLATE."/".DEFAULT_DESIGN."/index.html");
		echo $inf;
	}


		



}
?>