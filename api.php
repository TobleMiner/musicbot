<?php
	if (array_key_exists("jsonapi", $_POST))
	{
		require_once( __DIR__ . "/api/apiJSON.php" );
		if(array_key_exists('data', $_POST))
			echo json_encode(( new BotApiJSON )->call(json_decode($_POST["data"])));
		else 
			echo json_encode(new CommandResult(FALSE, "JSON data missing", BotApi::API_MISSING_PARAMS));
	}
	else
	{
		require_once( __DIR__ . "/api/apiPOST.php" );
		echo BotApiPOST::call();
	}
?>
