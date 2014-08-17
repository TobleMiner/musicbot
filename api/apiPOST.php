<?php
	require_once( dirname(__FILE__) . '/BotApi.php' );

	class BotApiPOST extends BotApi
	{
		public static function call()
		{
			session_start();
			$res = new stdClass();
			$res->result = self::API_SUCCESS;
			if (
				array_key_exists("loggedin", $_SESSION) && $_SESSION[ "loggedin" ] === TRUE
				&& array_key_exists("userid", $_SESSION)
				&& $_SESSION[ "userid" ] !== FALSE
			)
			{
				if (!array_key_exists("action", $_POST))
				{
					$res->result = self::API_MISSING_PARAMS;
					die();
				}
				$action = $_POST[ "action" ];
				$sessionuserid = $_SESSION[ "userid" ];
				require_once( __DIR__ . "/../util/user.php" );
				$apiuser = new User( $sessionuserid );
				if (array_key_exists("needsadmin", $_POST))
				{
					if ($apiuser->perms->canAdmin)
					{
						if ($action == "adduser")
						{
							if (array_key_exists("username", $_POST) && array_key_exists("password", $_POST))
							{
								try
								{
									User::addUser($_POST[ "username" ], $_POST[ "password" ]);
								}
								catch ( Exception $e )
								{
									$res->result = self::API_INTERNAL_ERROR;
								}
							}
							else
							{
								$res->result = self::API_MISSING_PARAMS;
							}
						}
						else
						{
							if (!array_key_exists("userid", $_POST))
							{
								$res->result = self::API_MISSING_PARAMS;
							}
							else
							{
								$user = NULL;
								try
								{
									$user = new User( $_POST[ "userid" ] );
								}
								catch ( Exception $e )
								{
									$res->result = self::API_INVALID_USERID;
								}
								if ($user != NULL)
								{
									if ($action == "setpassword")
									{
										if (!array_key_exists("password", $_POST))
										{
											$res->result = self::API_MISSING_PARAMS;
										}
										else
										{
											try
											{
												$user->changePassword($_POST[ "password" ]);
											}
											catch ( Exception $e )
											{
												$res->result = self::API_INTERNAL_ERROR;
											}
										}
									}
									elseif ($action == "setadmin")
									{
										if (!array_key_exists("state", $_POST))
										{
											$res->result = self::API_MISSING_PARAMS;
										}
										else
										{
											try
											{
												if ($_POST[ "state" ] == "true")
												{
													$user->grantPermission(0);
												}
												else
												{
													$user->revokePermission(0);
												}
												$res->result = self::API_SUCCESS;
											}
											catch ( Exception $e )
											{
												$res->result = self::API_INTERNAL_ERROR;
											}
										}
									}
									elseif ($action == "setcontrol")
									{
										if (!array_key_exists("state", $_POST))
										{
											$res->result = self::API_MISSING_PARAMS;
										}
										else
										{
											try
											{
												if ($_POST[ "state" ] == "true")
												{
													$user->grantPermission(1);
												}
												else
												{
													$user->revokePermission(1);
												}
												$res->result = self::API_SUCCESS;
											}
											catch ( Exception $e )
											{
												$res->result = self::API_INTERNAL_ERROR;
											}
										}
									}
									elseif ($action == "deluser")
									{
										try
										{
											$user->delete();
											$res->result = self::API_SUCCESS;
										}
										catch ( Exception $e )
										{
											$res->result = self::API_INTERNAL_ERROR;
										}
									}
								}
							}
						}
					}
					else
					{
						$res->result = self::API_ACCESS_DENIED;
					}
				}
				else
				{
					require_once( __DIR__ . "/../config/player.php" );
					$player = PlayerConfig::getPlayerInstance();
					if (array_key_exists("needscontrol", $_POST))
					{
						if ($apiuser->perms->canControl)
						{
							if ($action == "play")
							{
								if (array_key_exists("uri", $_POST))
								{
									try
									{
										$player->play($_POST[ "uri" ]);
									}
									catch ( Exception $e )
									{
										$res->result = self::API_INTERNAL_ERROR;
									}
								}
								else
								{
									$res->result = self::API_MISSING_PARAMS;
								}
							}
							elseif ($action == "pause")
							{
								try
								{
									$player->pause();
								}
								catch ( Exception $e )
								{
									$res->result = self::API_INTERNAL_ERROR;
								}
							}
							elseif ($action == "stop")
							{
								try
								{
									$player->stop();
								}
								catch ( Exception $e )
								{
									$res->result = self::API_INTERNAL_ERROR;
								}
							}
							elseif ($action == "setvolume")
							{
								if (array_key_exists("volume", $_POST))
								{
									try
									{
										$player->setVolume($_POST[ "volume" ]);
									}
									catch ( Exception $e )
									{
										$res->result = self::API_INTERNAL_ERROR;
									}
								}
								else
								{
									$res->result = self::API_MISSING_PARAMS;
								}
							}
							elseif ($action == "setaudiopos")
							{
								if (array_key_exists("pos", $_POST))
								{
									try
									{
										$player->setAudioPos($_POST[ "pos" ]);
									}
									catch ( Exception $e )
									{
										$res->result = self::API_INTERNAL_ERROR;
									}
								}
								else
								{
									$res->result = self::API_MISSING_PARAMS;
								}
							}
							else
							{
								$res->result = self::API_UNKNOWN_ACTION;
							}
						}
					}
					else
					{
						$res->result = self::API_SUCCESS;
						if ($action == "ispaused")
						{
							$res->paused = $player->isPaused()->raw ? TRUE : FALSE;
						}
						elseif ($action == "getstatus")
						{
							$res = $player->getStatus()->raw;
							$res->result = self::API_SUCCESS;
						}
						elseif ($action == "getvolume")
						{
							$res->volume = (int)$player->getVolume()->raw;
						}
						elseif ($action == "gettitle")
						{
							$res->title = $player->getTitle()->raw;
						}
						elseif ($action == "isplaying")
						{
							$res->playing = $player->isPlaying()->raw ? TRUE : FALSE;
						}
						elseif ($action == "getvolumelimits")
						{
							$res = $player->getVolumeLimits()->raw;
							$res->result = self::API_SUCCESS;
						}
						elseif ($action == "getaudiolength")
						{
							$res->audiolen = (int)$player->getAudioLength()->raw;
						}
						elseif ($action == "getaudiopos")
						{
							$res->audiopos = (int)$player->getAudioPos()->raw;
						}
						else
						{
							$res->result = self::API_UNKNOWN_ACTION;
						}
					}
				}
			}
			else
			{
				$res->result = self::API_INVALID_SESSION;
			}
			echo json_encode($res);
		}
	}

?>
