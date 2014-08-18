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

      protected function getStatusArray() {
         $data = $this->getStatus();

         if($data->raw !== false)
         {
            $status = [];
            $lines = preg_split('$\n$', $data->raw);
            foreach($lines as $line)
            {
               $status_tmp = null;
               preg_match('$[\n]*(?P<name>[a-z_]+):[ ]+(?P<value>.*)[\n]*$', trim($line), $status_tmp);
               $status[$status_tmp['name']] = $status_tmp['value'];
            }
            $data->raw = $status;
         }

         return $data;
      }

		public function isPlaying()
		{
         $data = $this->getStatusArray();
         if($data->raw !== false)
         {
            $data->raw = $data->raw['state'] == 'play' ? TRUE : FALSE;
            $data->userFriendly = $data->raw ? 'Playing' : 'Not playing';
         }
         return $data;
		}

		public function play($uri)
		{
         $this->telnet->exec(['clear', 'add '.$uri, 'play']);
         return $this->isPlaying();
		}

		public function stop()
		{
         return $this->telnet->exec('stop');
		}

		public function pause()
		{
         return $this->telnet->exec('pause 1');
		}

		public function prev()
		{
         return $this->telnet->exec('previous');
		}

		public function next()
		{
         return $this->telnet->exec('next');
		}

		public function isPaused()
		{
         $data = $this->getStatusArray();
         if($data->raw !== false)
         {
            $data->raw = $data->raw['state'] == 'pause' ? TRUE : FALSE;
            $data->userFriendly = $data->raw ? 'Paused' : 'Not paused';
         }
         return $data;
		}

		public function getStatus()
		{
         return $this->telnet->exec('status');
		}

		public function getVolume()
		{
			// TODO: Implement getVolume() method.
		}

		public function getTitle()
		{
         $obj = $this->telnet->exec('currentsong');
         if($obj->raw == "") $obj->userFriendly = "Currently not playing :(";
         return $obj;
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
         $data = $this->getStatusArray();
         if($data->raw !== false)
         {
            $data->raw = $data->raw['time'];
            $data->userFriendly = $data->raw ? 'Get audiopos success' : 'Get audiopos failed!';
         }
         return $data;
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

         $this->clearBuffer();
         $this->setPromptstr('OK');
         parent::exec('ping');
         $this->setPromptstr('');
         $this->clearBuffer();
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
			preg_match('$ACK \[(?P<error_code>[0-9]+)\@(?P<command_list_num>[0-9]+)\] \{(?P<current_command>[a-zA-Z0-9_]*)\} (?P<message_text>.*)$', $response, $error_data);

			if (sizeof($error_data) > 0)
			{
				return new CommandResult( FALSE, 'Error ' . $error_data['error_code'] . ': ' . $error_data['message_text'], BotApi::API_INTERNAL_ERROR );
			}

			if (preg_match('$OK$', $response))
			{
            if(preg_match('$^MPD [0-9\.]+\n$', $response))
               $response = preg_replace('$MPD [0-9\.]+\n$', '',$response);

				$response_text = preg_replace('$[\n]*OK[\n]*$', '', $response);
				return new CommandResult( $response_text, "", BotApi::API_SUCCESS );
			}

			return new CommandResult( FALSE, "Fatal Error!", BotApi::API_INTERNAL_ERROR );
		}

      /**
       * @param string|string[] $cmd
       *
       * @return CommandResult|string
       */
      public function exec($cmd)
		{
         $commands = is_array($cmd) ? $cmd : [$cmd];
         $ret = null;

         foreach($commands as $command)
         {
            $ret = $this->parseResponse(parent::exec($command));
            if($ret->raw === false)
               return $ret;
         }
         
         return $ret;
		}
	}

?>
