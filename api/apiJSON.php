<?php
	class BotApiJSON
	{
		private $cmdLogin;
		private $cmdLogout;

		public function __construct()
		{
			$this -> res = new stdClass();
			$this -> uid = NULL;
			require_once(__DIR__."/../util/command.php");
			require_once(__DIR__."/../config/player.php");
			$this -> player = PlayerConfig::getPlayerInstance();
			$this -> cmdLogin = new Command("login", FALSE, FALSE, array($this, "login"), "Logs you in. Usage: login <username> <password>", 2);
			$this -> cmdLogout = new Command("logout", TRUE, FALSE, array($this, "logout"), "Logs you out. Usage: logout");
			$this -> cmdHelp = new Command("help", FALSE, FALSE, array($this, "help"), "Prints a command list with command usage information. Usage: help");
		}

		public function call($jsonobj)
		{
			$res = $this -> res;
			$res -> result = 4;
			if($jsonobj != NULL && isset($jsonobj -> reqstr) && isset($jsonobj -> uid))
			{
				$this -> uid = $jsonobj -> uid;
				$args = CommandUtil::parseArguments($jsonobj -> reqstr);
				if(sizeof($args) > 0)
				{
					$cmd = $this -> getCommands($this -> player)[strtolower(array_shift($args))];
					if($cmd != NULL)
					{
						if($cmd -> argnum == sizeof($args))
						{
							if($cmd -> requiresLogin)
							{
								require_once(__DIR__."/../util/login.php");
								$auth = new AuthHandler();
								$user = $auth -> getSession($this -> uid);
								if($user !== FALSE)
								{
									if($user -> perms -> has($cmd -> permid))
									{
										try
										{
											$res = call_user_func_array($cmd -> callback, $args);
										}
										catch(Exception $e)
										{
											$res = new CommandResult(FALSE, "Internal error.");
											$res -> result = 1;
										}
									}
									else
									{
										$res = new CommandResult(FALSE, "Operation not permitted.");
										$res -> result = 3;
									}
								}
								else
								{
									$res = new CommandResult(FALSE, "Login required.");
									$res -> result = 8;
								}
							}
							else
							{
								try
								{
									$res = call_user_func_array($cmd -> callback, $args);
								}
								catch(Exception $e)
								{
									$res -> result = 1;
								}
							}
						}
						else
						{
							$res = new CommandResult(FALSE, sprintf("Wrong number of arguments: %s", $cmd -> help));
							$res -> result = 4;
						}
					}
					else
					{
						$res = new CommandResult(FALSE, "Unknown command");
						$res -> result = 2;
					}
				}
				else
				{
					$res = new CommandResult(FALSE, "Too few arguments: Command not given");
					$res -> result = 4;
				}
			}
			else
			{
				$res = new CommandResult(FALSE, "Too few arguments: Not a valid request");
				$res -> result = 4;
			}
			return json_encode($res);
		}

		private function login($username, $password)
		{
			$res = $this -> res;
			require_once(__DIR__."/../util/login.php");
			$auth = new AuthHandler();
			$result = 0;
			if(func_num_args() == 2)
			{
				$userid = $auth -> login($username, $password);
				if($userid !== FALSE)
				{
					require_once(__DIR__."/../config/auth.php");								
					$auth -> createSession($userid, $this -> uid, time() + AuthConf::$loginSessionLength);
					return new CommandResult(TRUE, "Login successful");
				}
				else
				{
					$result = 7;
				}
			}
			else
			{
				$result = 4;
			}
			return new CommandResult(TRUE, "Login failed", $result);
		}

		private function logout()
		{
			require_once(__DIR__."/../util/login.php");
			$auth = new AuthHandler();
			$auth -> deleteSession($this -> uid);
			$this -> res -> result = 0;
			return new CommandResult(TRUE, "You have successfully logged out.");
		}

		public function help()
		{
			$help = Array();
			foreach($this -> getCommands($this -> player) as $cmd)
			{
				array_push($help, sprintf("%s: %s", $cmd -> cmd, $cmd -> help));
			}
			return new CommandResult(TRUE, implode("\n", $help));
		}

		public function getCommands($player)
		{
			$cmds = $player -> getCommands();
			$cmds["login"] = $this -> cmdLogin;
			$cmds["logout"] = $this -> cmdLogout;
			$cmds["help"] = $this -> cmdHelp;
			return $cmds;
		}
	}
?>