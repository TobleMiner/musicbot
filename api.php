<?php
	if (array_key_exists("jsonapi", $_POST))
	{
		require_once( __DIR__ . "/api/apiJSON.php" );
      if(array_key_exists('data', $_POST))
		   echo ( new BotApiJSON )->call(json_decode($_POST["data"]));
	}
	else
	{
		require_once( __DIR__ . "/api/apiPOST.php" );
		echo BotApiPOST::call();
	}
?>
