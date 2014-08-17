<?php
	class TimeUtil
	{
		public static function secondsToHumanReadable($secs)
		{
			return (new DateTime("@0")) -> diff(new DateTime("@$secs")) -> format("%h:%i:%s");
		}

		public static function getMicrotime()
		{
			return explode(" ", microtime())[0];
		}
	}
?>