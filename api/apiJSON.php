<?php
	require_once(__DIR__ . '/BotApi.php');

	class BotApiJSON extends BotApi
	{
		/**
		 * @var Command
		 */
		private $cmdLogout;

		public function __construct()
		{
			$this -> res = new stdClass();
			$this -> uid = NULL;
			require_once(__DIR__ . "/../util/command.php");
			require_once(__DIR__ . "/../config/player.php");
			$this -> player = PlayerConfig::getPlayerInstance();
			$this -> cmdLogin = new Command("login", FALSE, FALSE, array($this, "login"), 'login',
											new CmdArg('username', 'username'), new CmdArg('password', 'password'));
			$this -> cmdLogout = new Command("logout", TRUE, FALSE, array($this, "logout"), 'logout');
			$this -> cmdHelp = new Command("help", FALSE, FALSE, array($this, "help"), 'help');
		}

		/**
		 * @param StdClass $jsonobj
		 *
		 * @return CommandResult
		 */
		public function call($jsonobj)
		{
			$res = $this->res;
			$res->result = self::API_MISSING_PARAMS;
			if ($jsonobj != NULL && isset( $jsonobj->reqstr ) && isset( $jsonobj->uid ))
			{
				$this->uid = $jsonobj->uid;
				$args = CommandUtil::parseArguments($jsonobj->reqstr);
				if (sizeof($args) > 0)
				{
					$cmd = $this->getCommands($this->player)[strtolower(array_shift($args))];
					if ($cmd != NULL)
					{
//						if ($cmd->argnum == sizeof($args))
//						{
							if ($cmd->requiresLogin)
							{
								require_once( __DIR__ . "/../util/login.php" );
								$auth = new AuthHandler();
								$user = $auth->getSession($this->uid);
								if ($user !== FALSE)
								{
									if ($user->perms->has($cmd->permid))
									{
										try
										{
											$res = call_user_func_array($cmd->callback, $args);
										}
										catch ( Exception $e )
										{
											$res->result = self::API_INTERNAL_ERROR;
										}
									}
									else
									{
										$res = new CommandResult( FALSE, "Operation not permitted." );
										$res->result = self::API_ACCESS_DENIED;
									}
								}
								else
								{
									$res = new CommandResult( FALSE, "Login required." );
									$res->result = self::API_LOGIN_REQUIRED;
								}
							}
							else
							{
								try
								{
									$res = call_user_func_array($cmd->callback, $args);
								}
								catch ( Exception $e )
								{
									$res->result = self::API_INTERNAL_ERROR;
								}
							}
						}
						else
						{
							$res = new CommandResult( FALSE, sprintf("Wrong number of arguments: %s", $cmd->help) );
							$res->result = self::API_MISSING_PARAMS;
						}
					}
					else
					{
						$res = new CommandResult( FALSE, "Unknown command" );
						$res->result = self::API_UNKNOWN_ACTION;
					}
//				}
//				else
//				{
//					$res = new CommandResult( FALSE, "Too few arguments: Command not given" );
//					$res->result = self::API_MISSING_PARAMS;
//				}
			}
			else
			{
				$res = new CommandResult( FALSE, "Too few arguments: Not a valid request" );
				$res->result = self::API_MISSING_PARAMS;
			}

			return $res;
		}

		/**
		 * @param String $username
		 * @param String $password
		 *
		 * @return CommandResult
		 */
		private function login($username, $password)
		{
			$res = $this->res;
			require_once( __DIR__ . "/../util/login.php" );
			$auth = new AuthHandler();
			$result = self::API_SUCCESS;
			if (func_num_args() == 2)
			{
				$userid = $auth->login($username, $password);
				if ($userid !== FALSE)
				{
					require_once( __DIR__ . "/../config/auth.php" );
					$auth->createSession($userid, $this->uid, time() + AuthConf::$loginSessionLength);
					return new CommandResult( TRUE, "Login successful" );
				}
				else
				{
					$result = self::API_INVALID_LOGIN;
				}
			}
			else
			{
				$result = self::API_MISSING_PARAMS;
			}
			return new CommandResult( TRUE, "Login failed", $result );
		}

		/**
		 * @return CommandResult
		 */
		private function logout()
		{
			require_once( __DIR__ . "/../util/login.php" );
			$auth = new AuthHandler();
			$auth->deleteSession($this->uid);
			$this->res->result = self::API_SUCCESS;
			return new CommandResult( TRUE, "You have successfully logged out." );
		}

		/**
		 * @return CommandResult
		 */
		public function help()
		{
			$help = Array();
			foreach ($this->getCommands($this->player) as $cmd)
			{
				array_push($help, sprintf("%s: %s", $cmd->cmd, $cmd->help));
			}
			return new CommandResult( TRUE, implode("\n", $help) );
		}

		/**
		 * @param Player $player
		 *
		 * @return Command[]
		 */
		public function getCommands($player)
		{
			$cmds = $player->getCommands();
			$cmds["login"] = $this->cmdLogin;
			$cmds["logout"] = $this->cmdLogout;
			$cmds["help"] = $this->cmdHelp;
			return $cmds;
		}
	}

?>