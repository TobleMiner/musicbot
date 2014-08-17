<?php
	class CommandUtil
	{
		public static function parseArguments($argstr)
		{
			$chars = str_split($argstr);
			$quoted = FALSE;
			$pos = 0;
			$args = Array();
			$arg = "";
			foreach ($chars as $char)
			{
				if($char == " " && !$quoted)
				{
					array_push($args, $arg);
					$arg = "";
				}
				else
				{
					if($char == "\"")
					{
						if($pos > 0)
						{
							if($chars[$pos - 1] == "\\")
							{
								if(strlen($arg) > 0)
									$arg = substr($arg, 0, -1);
								$arg .= $char;
							}
							else
								$quoted = !$quoted;
						}
						else
							$quoted = !$quoted;
					}
					else
					{
						$arg .= $char;
					}
				}
				$pos++;
			}
			array_push($args, $arg);
			return $args;
		}
	}

	class Command
	{
		public function __construct($cmd, $requiresLogin, $permid, $callback, $help = "", $argnum = 0)
		{
			$this -> cmd = $cmd;
			$this -> requiresLogin = $requiresLogin;
			$this -> permid = $permid;
			$this -> callback = $callback;
			$this -> help = $help;
			$this -> argnum = $argnum;
		}
	}

	class CommandResult
	{
		public function __construct($raw, $friendly, $result = 0)
		{
			$this -> raw = $raw;
			$this -> userFriendly = $friendly;
			$this -> result = $result;
		}
	}
?>