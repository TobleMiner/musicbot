<?php
	class PlayerConfig
	{
		public static $player = "vlc.php";

		public static function getPlayerInstance()
		{
			require_once(__DIR__."/../player/".self::$player);
			return new VLCPlayer();
		}
	}
?>