<?php
	require_once("player.php");
	require_once(__DIR__."/../util/telnet.php");

	class VLCPlayer extends Player
	{
		public function __construct()
		{
			require_once(__DIR__."/../config/vlc.php");
			require_once(__DIR__."/../util/time.php");
			require_once(__DIR__."/../util/command.php");
			$this -> telnet = new TelnetVLC(VLConfig::$host, VLConfig::$port, "");
			$this -> telnet -> login(VLConfig::$password);
		}

		public function play($uri)
		{
			$resobj = new stdClass();
			$uri = explode("\n", $uri)[0];
			if($this -> isPlaying() -> raw)
				$this -> telnet -> exec("stop");
			$this -> telnet -> exec("clear");
			$this -> telnet -> exec("add $uri");
			$this -> telnet -> exec("play");
			$isplaying = $this -> isPlaying() -> raw;
			return new CommandResult($isplaying, ($isplaying ? sprintf("Now playing: '%s'", $this -> getTitle() -> raw) : sprintf("Failed to open '%s'", $uri)));
		}

		public function isPlaying()
		{
			$isplaying = (((int)trim($this -> telnet -> exec("is_playing"))) == 1);
			return new CommandResult($isplaying, (sprintf("Playing: %s", $isplaying ? "yes" : "no")));
		}

		public function isPaused()
		{
			$paused = FALSE;
			if($this -> isPlaying() -> raw)
			{
				$status = explode("\n", $this -> telnet -> exec("status"));
				$paused = strpos($status[sizeof($status) - 1], "paused") !== FALSE;
			}
			return new CommandResult($paused, (sprintf("Paused: %s", ($paused ? "yes" : "no"))));
		}

		public function stop()
		{
			$this -> telnet -> exec("stop");
			$isstopped = !$this -> isPlaying() -> raw;
			return new CommandResult($isstopped, ($isstopped || TRUE ? "Playback stopped." : "Failed stopping playback."));
		}

		public function pause()
		{
			$pre = $this -> isPaused();
			$this -> telnet -> exec("pause");
			$success = $this -> isPaused() -> raw != $pre;
			return new CommandResult($success, ($success ? "Pause toggled." : "Failed toggeling pause."));
		}

		public function getStatus()
		{
			$status = new PlayerStatus(	$this -> isPlaying() -> raw,
										$this -> getTitle() -> raw, 
										$this -> getVolume() -> raw, 
										$this -> isPaused() -> raw, 
										$this -> getAudioPos() -> raw, 
										$this -> getAudioLength() -> raw);
			$fstring = <<<EOT
Playing: %s
Title: '%s'
Volume: %s
Paused: %s
AudioPos: %s
AudioLength: %s
EOT;
			return new CommandResult($status,
											sprintf($fstring,
													($status -> playing ? "yes" : "no"),
													$status -> title,
													(string)$status -> volume,
													($status -> paused ? "yes" : "no"),
													(($status -> audiopos > -1) ? (string)TimeUtil::secondsToHumanReadable($status -> audiopos) : "--"),
													(($status -> audiolen > -1) ? (string)TimeUtil::secondsToHumanReadable($status -> audiolen) : "--")));
		}

		public function getTitle()
		{
			$title = $this -> telnet -> exec("get_title");
			return new CommandResult($title, sprintf("Title: %s", $title));
		}

		public function getVolume()
		{
			$volume = $this -> telnet -> exec("volume");
			return new CommandResult((int)$volume, sprintf("Volume: %s", $volume));
		}

		public function setVolume($vol)
		{
			$vol = preg_replace("/[^0-9]/", '', $vol);
			$this -> telnet -> exec("volume $vol");
			$success = $vol == $this -> getVolume() -> raw;
			return new CommandResult($success, ($success ? "Volume successfully set" : "Failed setting volume"));
		}

		public function getVolumeLimits()
		{
			$limits = new VolumeLimit(0, 256);
			return new CommandResult($limits, sprintf("Volume limits: Upper: %s Lower: %s", $limits -> lower, $limits -> upper));
		}

		public function getAudioLength()
		{
			$len = -1;
			if($this -> isPlaying() -> raw)
				$len = $this -> telnet -> exec("get_length");
			return new CommandResult((int)$len, (((int)$len > -1) ? TimeUtil::secondsToHumanReadable((int)$len) : "--"));
		}

		public function getAudioPos()
		{
			$pos = -1;
			if($this -> isPlaying() -> raw)
				$pos = $this -> telnet -> exec("get_time");
			return new CommandResult((int)$pos, (((int)$pos > -1) ? TimeUtil::secondsToHumanReadable((int)$pos) : "--"));
		}

		public function setAudioPos($pos)
		{
			$pos = preg_replace("/[^0-9]/", '', $pos);
			$mtime = TimeUtil::getMicrotime();
			$this -> telnet -> exec("seek $pos");
			$success = $pos + floor((TimeUtil::getMicrotime() - $mtime) / 1000000) == $this -> getAudioPos() -> raw;
			return new CommandResult($success, ($success ? "Position successfully set" : "Failed setting position"));
		}

		public function getAudioInfo()
		{
			$audioinfo = $this -> telnet -> exec("info");
			return new CommandResult($audioinfo, $audioinfo);
		}

		public function prev()
		{
			$this -> telnet -> exec("prev");
			return new CommandResult(TRUE, "Playing previous title in playlist");
		}

		public function next()
		{
			$this -> telnet -> exec("next");
			return new CommandResult(TRUE, "Playing next title in playlist");
		}

		public function __destruct()
		{
			$this -> telnet -> disconnect();
		}
	}

	class TelnetVLC extends Telnet
	{
		public function login($password)
		{
			try
			{
				$this -> setPromptstr('Password:');
				$this -> waitPromptstr();
				$this -> write($password);
				$this -> setPromptstr("> ");
				$this -> waitPromptstr();
			}
			catch(Exception $e)
			{
				throw $e;
				throw new Exception("Login failed.");
			}
			return TRUE;
		}

		public function exec($cmd)
		{
			$this -> clearBuffer();
			return parent::exec($cmd);
		}
	}
?>