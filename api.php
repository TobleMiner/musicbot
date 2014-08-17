<?php
	if(array_key_exists("jsonapi", $_POST))
	{
		require_once(__DIR__."/api/apiJSON.php");
		echo json_encode((new BotApiJSON) -> call(json_decode($_POST["data"])));
	}
	else
	{
		require_once(__DIR__."/api/apiPOST.php");
		echo BotApiPOST::call();
	}
?>