<?php
//phpinfo();
	if (strlen($_SERVER['REQUEST_URI']) > 255 ||
		strpos($_SERVER['REQUEST_URI'], "eval(") ||
		strpos($_SERVER['REQUEST_URI'], "CONCAT") ||
		strpos($_SERVER['REQUEST_URI'], "UNION+SELECT") ||
		strpos($_SERVER['REQUEST_URI'], "base64")) {
	header("HTTP/1.1 414 Request-URI Too Long");
	header("Status: 414 Request-URI Too Long");
	header("Connection: Close");
	exit;
	}
	
	
	include ("ini.php");
	include ("system/kernel.class.php");
	include ("system/module.class.php");
	include ("system/display.class.php");


	
	ini_set('url_rewriter.tags', 'none');
	session_cache_expire(60*60*24*7);
	session_start();
	$expiry = 60*60*24*100;
	setcookie(session_name(), session_id(), time()+$expiry, "/");

	if ((REDIR_WWW == true) && (!preg_match("/^www\\./", $_SERVER['HTTP_HOST'])))
    	$kernel->priv_redirect_301("http://www.".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

	$kernel = new kernel();
	$module = new module();
	$display = new display();

	
	$user = $kernel->login();
	$url = $kernel->parser_url();
	$url = $module->set_pagename($url);
	$design = $module->set_design($url[1]);
	$page = $module->parser_modules($design);

	$page = $display->parser_lang($page);
	echo $page;
?>