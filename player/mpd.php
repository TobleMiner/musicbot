<?php
	require_once( "player.php" );
	require_once( __DIR__ . "/../util/telnet.php" );
	require_once( __DIR__ . '/../api/BotApi.php' );

	class MPD extends Player
	{

		/**
		 * @var Telnet
		 */
		protected $telnet;

		public function __construct()
		{
			parent::__construct();
			require_once( __DIR__ . '/../config/MPDConfig.php' );
			$this->telnet = new MPDTelnet( MPDConfig::$host, MPDConfig::$port, '' );
		}

		public function isPlaying()
		{
			// TODO: Implement isPlaying() method.
		}

		public function play($uri)
		{
			return $this->telnet->exec("play");
		}

		public function stop()
		{
         return $this->telnet->exec("stop");
		}

		public function pause()
		{
         return $this->telnet->exec("pause 1");
		}

		public function prev()
		{
         return $this->telnet->exec("previous");
		}

		public function next()
		{
         return $this->telnet->exec("next");
		}

		public function isPaused()
		{
			// TODO: Implement isPaused() method.
		}

		public function getStatus()
		{
         return $this->telnet->exec("status");
		}

		public function getVolume()
		{
			// TODO: Implement getVolume() method.
		}

		public function getTitle()
		{
         return $this->telnet->exec("currentsong");
		}

		public function setVolume($vol)
		{
			// TODO: Implement setVolume() method.
		}

		public function getVolumeLimits()
		{
			// TODO: Implement getVolumeLimits() method.
		}

		public function getAudioLength()
		{
			// TODO: Implement getAudioLength() method.
		}

		public function getAudioPos()
		{
			// TODO: Implement getAudioPos() method.
		}

		public function setAudioPos($pos)
		{
			// TODO: Implement setAudioPos() method.
		}
	}

	class MPDTelnet extends Telnet
	{
		public function connect()
		{
			parent::connect();

			if (MPDConfig::$password)
			{
				$this->login(MPDConfig::$password);
			}
		}

		public function login($password)
		{
			$this->exec('password ' . $password);
		}

		/**
		 * @param $response
		 *
		 * @return CommandResult
		 */
		public function parseResponse($response)
		{
			// http://www.musicpd.org/doc/protocol/ch01s03.html
			// 0 => error_code
			// 1 => command_list_num
			// 2 => current_command
			// 3 => message_text
			$error_data = NULL;
			preg_match('$ACK \[(?P<error_code>[0-9]+)\@(?P<command_list_num>[0-9]+)\] \{(?P<current_command>[a-zA-Z0-9_]*)\} (?P<message_text>.*?)\n$', $response, $error_data);

			if (sizeof($error_data) > 0)
			{
				return new CommandResult( FALSE, 'Error ' . $error_data['error_code'] . ': ' . $error_data['message_text'], BotApi::API_INTERNAL_ERROR );
			}

			if (preg_match('$OK$', $response))
			{
				$response_text = preg_replace('$/\nOK\n$', '', $response);
				return new CommandResult( $response_text, "", BotApi::API_SUCCESS );
			}

			return new CommandResult( FALSE, "Fatal Error!", BotApi::API_INTERNAL_ERROR );
		}

		public function exec($cmd)
		{
			return $this->parseResponse(parent::exec($cmd));
		}
	}

?>
