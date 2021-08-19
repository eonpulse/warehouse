<?php
class widget
{

    function widget($name)
    {
        echo "<br>Start widget ".$name."</b>";
    }

	function set_modulename($url)
	{
		if(!file_exists(PATH_MODULES."/".$name."/start.php")) {
    		$url[1] = "error";
    		$url[2] = "404";
		}
        $this->url = $url;
		return $url;
	}


	function start_module()
	{
		include(PATH_MODULES."/".$this->url[1]."/start.php");
		return $this->inf;
	}







}
?>