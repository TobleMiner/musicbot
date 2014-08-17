<?php
	class BotApiPOST
	{
		public static function call()
		{
			session_start();
			$res = new stdClass();
			$res -> result = 0;
			if(array_key_exists("loggedin", $_SESSION) && $_SESSION["loggedin"] === TRUE && array_key_exists("userid", $_SESSION) && $_SESSION["userid"] !== FALSE)
			{
				if(!array_key_exists("action", $_POST))
				{
					$res -> result = 4;
					die();
				}
				$action = $_POST["action"];
				$sessionuserid = $_SESSION["userid"];
				require_once(__DIR__."/../util/user.php");
				$apiuser = new User($sessionuserid);
				if(array_key_exists("needsadmin", $_POST))
				{
					if($apiuser -> perms -> canAdmin)
					{
						if($action == "adduser")
						{
							if(array_key_exists("username", $_POST) && array_key_exists("password", $_POST))
							{
								try
								{
									User::addUser($_POST["username"], $_POST["password"]);
								}
								catch(Exception $e)
								{
									$res -> result = 1;
								}
							}
							else
							{
								$res -> result = 4;
							}
						}
						else
						{
							if(!array_key_exists("userid", $_POST))
							{
								$res -> result = 4;
							}
							else
							{
								$user = null;
								try
								{
									$user = new User($_POST["userid"]);
								}
								catch(Exception $e)
								{
									$res -> result = 5;
								}
								if($user != null)
								{
									if($action == "setpassword")
									{
										if(!array_key_exists("password", $_POST))
										{
											$res -> result = 4;
										}
										else
										{
											try
											{
												$user -> changePassword($_POST["password"]);
											}
											catch(Exception $e)
											{
												$res -> result = 1;
											}
										}
									}
									elseif($action == "setadmin")
									{
										if(!array_key_exists("state", $_POST))
										{
											$res -> result = 4;
										}
										else
										{
											try
											{
												if($_POST["state"] == "true")
												{
													$user -> grantPermission(0);
												}
												else
												{
													$user -> revokePermission(0);
												}
												$res -> result = 0;
											}
											catch(Exception $e)
											{
												$res -> result = 1;
											}
										}
									}
									elseif($action == "setcontrol")
									{
										if(!array_key_exists("state", $_POST))
										{
											$res -> result = 4;
										}
										else
										{
											try
											{
												if($_POST["state"] == "true")
												{
													$user -> grantPermission(1);
												}
												else
												{
													$user -> revokePermission(1);
												}
												$res -> result = 0;
											}
											catch(Exception $e)
											{
												$res -> result = 1;
											}
										}
									}
									elseif($action == "deluser")
									{
										try
										{
											$user -> delete();
											$res -> result = 0;
										}
										catch(Exception $e)
										{
											$res -> result = 1;
										}
									}
								}
							}
						}
					}
					else
					{
						$res -> result = 3;
					}
				}
				else
				{
					require_once(__DIR__."/../config/player.php");
					$player = PlayerConfig::getPlayerInstance();
					if(array_key_exists("needscontrol", $_POST))
					{
						if($apiuser -> perms -> canControl)
						{
							if($action == "play")
							{
								if(array_key_exists("uri", $_POST))
								{
									try
									{
										$player -> play($_POST["uri"]);
									}
									catch(Exception $e)
									{
										$res -> result = 1;
									}
								}
								else
								{
									$res -> result = 4;
								}
							}
							elseif($action == "pause")
							{
								try
								{
									$player -> pause();
								}
								catch(Exception $e)
								{
									$res -> result = 1;
								}
							}
							elseif($action == "stop")
							{
								try
								{
									$player -> stop();
								}
								catch(Exception $e)
								{
									$res -> result = 1;
								}
							}
							elseif($action == "setvolume")
							{
								if(array_key_exists("volume", $_POST))
								{
									try
									{
										$player -> setVolume($_POST["volume"]);
									}
									catch(Exception $e)
									{
										$res -> result = 1;
									}
								}
								else
								{
									$res -> result = 4;
								}
							}
							elseif($action == "setaudiopos")
							{
								if(array_key_exists("pos", $_POST))
								{
									try
									{
										$player -> setAudioPos($_POST["pos"]);
									}
									catch(Exception $e)
									{
										$res -> result = 1;
									}
								}
								else
								{
									$res -> result = 4;
								}
							}
							else
								$res -> result = 2;
						}
					}
					else
					{
						$res -> result = 0;
						if($action == "ispaused")
						{
							$res -> paused = $player -> isPaused() -> raw ? true : false;
						}
						elseif($action == "getstatus")
						{
							$res = $player -> getStatus() -> raw;
							$res -> result = 0;
						}
						elseif($action == "getvolume")
						{
							$res -> volume = (int)$player -> getVolume() -> raw;
						}
						elseif($action == "gettitle")
						{
							$res -> title = $player -> getTitle() -> raw;
						}
						elseif($action == "isplaying")
						{
							$res -> playing = $player -> isPlaying() -> raw ? true : false;
						}
						elseif($action == "getvolumelimits")
						{
							$res = $player -> getVolumeLimits() -> raw;
							$res -> result = 0;
						}
						elseif($action == "getaudiolength")
						{
							$res -> audiolen = (int)$player -> getAudioLength() -> raw;
						}
						elseif($action == "getaudiopos")
						{
							$res -> audiopos = (int)$player -> getAudioPos() -> raw;
						}
						else
							$res -> result = 2;
					}
				}
			}
			else
				$res -> result = 6;
			echo json_encode($res);
		}
	}
?>