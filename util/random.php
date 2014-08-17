<?php
	class Random
	{
		public static function getRandomString($len = 10)
		{
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		    $randomStr = '';
		    for (; $len > 0; $len--)
		        $randomStr .= $chars[rand(0, strlen($chars) - 1)];
		    return $randomStr;
		}
	}
?>