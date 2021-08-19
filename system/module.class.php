<?php
class module
{

	function module()
    {

    }

	function set_pagename($url)
	{
        	
    	$name = addslashes($url[1]);
    	/*
		$query = "SELECT * FROM pages WHERE `module` = '".$name."' LIMIT 1";
		$res = mysql_query ($query) or die ("MySQL error");
		$row=mysql_fetch_array($res);
		if($row['module'] == "") {
			$url[1] = "err";
			$url[2] = "404";
		}
		*/
		
		if(!file_exists(PATH_PAGE_CONTENT."/".$name."/start.php")) {
			$url[1] = "err";
			$url[2] = "404";
		}
		$this->url = $url;
		return $url;
	}

	function set_design($module)
	{
		if(file_exists(PATH_MODULES."/".$module."/design/design.html")) {
			return file_get_contents(PATH_MODULES."/".$module."/design/design.html");
		}
		return file_get_contents(PATH_PAGE_TEMPLATE."/".DEFAULT_DESIGN."/index.html");
	}	

	function get_module($module)
	{
		if(file_exists(PATH_MODULES."/".$module."/start.php")) {
			//return file_get_contents(PATH_MODULES."/".$module."/start.php");
			include(PATH_MODULES."/".$module."/start.php");
			return $this->module;
		}
		return "Модуль ".$module." не найден";
	}	


	//ищет значения в скобках
	function parser_modules($design)
	{
		$raz[0] = "{%";
		$raz[1] = "%}";
		preg_match_all("/\\".$raz[0]."[^\\".$raz[1]."]+\\".$raz[1]."/s", $design, $regs); 
		//var_dump($regs);
		$i = 0;
		while($regs[0][$i]){
			if($regs[0][$i]!="{%main_module%}") {
				$module = $this->get_module(substr($regs[0][$i],2,-2));
			} else {
				$module = $this->main_module();;
			}
			$design = str_replace($regs[0][$i],$module,$design);
			$i++;
		}
		return $design;
	}

	function main_module()
	{
		include(PATH_PAGE_CONTENT."/".$this->url[1]."/start.php");
		return $this->inf;
	}







}
?>