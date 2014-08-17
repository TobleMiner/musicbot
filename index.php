<?php
	require_once("util/login.php");
	require_once("ui/interface.php");
	require_once("ui/auth.php");
	require_once("util/lang.php");
	
	$login = new AuthHandler();
	$loggedin = false;
	if(array_key_exists("logout", $_GET))
	{
		session_start();
		$_SESSION["loggedin"] = FALSE;
		$_SESSION["userid"] = FALSE;
		$loggedin = FALSE;
		header("Location: ./", true, 302);
	}
	elseif(array_key_exists("username", $_POST) && array_key_exists("password", $_POST))
	{
		$userid = $login -> login($_POST["username"], $_POST["password"]);
		if($userid !== FALSE)
		{
			$loggedin = TRUE;
			session_start();
			$_SESSION["loggedin"] = TRUE;
			$_SESSION["userid"] = $userid;
		}
	}
	else
	{
		session_start();
		if(array_key_exists("loggedin", $_SESSION) && $_SESSION["loggedin"] === TRUE && array_key_exists("userid", $_SESSION) && $_SESSION["userid"] !== FALSE)
		{
			$loggedin = TRUE;
		}
	}
	if($loggedin === TRUE)
	{
		if(sizeof($_GET) == 0) header("Location: ./?control", true, 302);
		BotInterface::show();
	}
	else
	{
		AuthPage::show();
	}
?>