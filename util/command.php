<?php

	class CommandUtil
	{
		public static function parseArguments($argstr)
		{
			$chars = str_split($argstr);
			$quoted = FALSE;
			$pos = 0;
			$args = array();
			$arg = "";
			foreach ($chars as $char)
			{
				if ($char == " " && !$quoted)
				{
					array_push($args, $arg);
					$arg = "";
				}
				else
				{
					if ($char == "\"")
					{
						if ($pos > 0)
						{
							if ($chars[$pos - 1] == "\\")
							{
								if (strlen($arg) > 0)
								{
									$arg = substr($arg, 0, -1);
								}
								$arg .= $char;
							}
							else
							{
								$quoted = !$quoted;
							}
						}
						else
						{
							$quoted = !$quoted;
						}
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

	class CmdArg
	{
		public function __construct($name, $descr, $opt = FALSE)
		{
			$this -> name = $name;
			$this -> description = $descr;
			$this -> optional = $opt;
		}
	}

	class Command
	{

		/**
		 * @param string    $cmd
		 * @param bool      $requiresLogin
		 * @param int|false $permid
		 * @param array     $callback
		 * @param string    $help
		 */
		public function __construct($cmd, $requiresLogin, $permid, $callback, $help)
		{
			$this -> cmd = $cmd;
			$this -> requiresLogin = $requiresLogin;
			$this -> permid = $permid;
			$this -> callback = $callback;
			$this -> help = $help;
			if(func_num_args() > 5)
				$this -> args = array_slice(func_get_args(), 5);
			else
				$this -> args = array();
			$this -> minArgnum = 0;
			foreach ($this -> args as $arg)
				if(!$arg -> optional)
					$this -> minArgnum++;
		}
	}

	class CommandResult
	{
		public function __construct($raw, $friendly, $result = 0)
		{
			$this->raw = $raw;
			$this->userFriendly = $friendly;
			$this->result = $result;
		}
	}

?>