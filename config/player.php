<?php

	class PlayerConfig
	{
		// Edit this variable to your player
		// actually possible: vlc, mpd
		public static $player = 'vlc';

		/**
		 * @return Player
		 */
		public static function getPlayerInstance()
		{
			require_once( __DIR__ . '/../player/' . self::$player . '.php' );
			$playername = strtoupper(self::$player);
			return new $playername();
		}
	}

?>
