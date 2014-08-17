<?php
	abstract class Player
	{
		public function __construct()
		{
			require_once(__DIR__."/../util/command.php");
		}

		public function getCommands()
		{
			require_once(__DIR__."/../util/permission.php");
			$cmds = Array();
			$cmds["isplaying"] = new Command("isplaying", TRUE, FALSE, array($this, "isPlaying"), "Returns the current playback status. Usage: isplaying");
			$cmds["play"] = new Command("play", TRUE, Permissions::$permIdControl, array($this, "play"), "Starts playback. Returns a status or failure message. Usage: play <URI>", 1);
			$cmds["prev"] = new Command("prev", TRUE, Permissions::$permIdControl , array($this, "prev"), "Plays the previous title if in a playlist. Usage: prev");
			$cmds["next"] = new Command("next", TRUE, Permissions::$permIdControl , array($this, "next"), "Plays the next title if in a playlist. Usage: next");
			$cmds["stop"] = new Command("stop", TRUE, Permissions::$permIdControl, array($this, "stop"), "Stops playback. Returns a status or failure message. Usage: stop");
			$cmds["pause"] = new Command("pause", TRUE, Permissions::$permIdControl , array($this, "pause"), "Pauses / resumes playback. Returns a success or failure message. Usage: pause");
			$cmds["ispaused"] = new Command("ispaused", TRUE, FALSE, array($this, "isPaused"), "Returns wether playback is paused or not. Usage: ispaused");
			$cmds["getstatus"] = new Command("getstatus", TRUE, FALSE, array($this, "getStatus"), "Returns information about the current song. Usage: getstatus");
			$cmds["getvolume"] = new Command("getvolume", TRUE, FALSE, array($this, "getVolume"), "Returns the volume of the current volume setting. Usage: getvolume");
			$cmds["gettitle"] = new Command("gettitle", TRUE, FALSE, array($this, "getTitle"), "Returns the title of the current song. Usage: gettitle");
			$cmds["setvolume"] = new Command("setvolume", TRUE, Permissions::$permIdControl, array($this, "setVolume"), "Sets the playback volume of the current audio stream. Usage: setvolume <volume>", 1);
			$cmds["getvolumelimits"] = new Command("getvolumelimits", TRUE, FALSE, array($this, "getVolumeLimits"), "Returns the possible max and min volume setting. Usage: getvolumelimits");
			$cmds["getaudiolength"] = new Command("getaudiolength", TRUE, FALSE, array($this, "getAudioLength"), "Returns the length of the current audio stream. Usage: getaudiolength");
			$cmds["getaudiopos"] = new Command("getaudiopos", TRUE, FALSE, array($this, "getAudioPos"), "Returns the position in the current audio stream. Usage: getaudiopos");
			$cmds["setaudiopos"] = new Command("setaudiopos", TRUE, Permissions::$permIdControl, array($this, "setAudioPos"), "Sets the audio stream to the given position. Returns either a sucess or failure message. Usage: setaudiopos <pos>", 1);
			return $cmds;
		}

		public abstract function isPlaying();
		public abstract function play($uri);
		public abstract function stop();
		public abstract function pause();
		public abstract function prev();
		public abstract function next();
		public abstract function isPaused();
		public abstract function getStatus();
		public abstract function getVolume();
		public abstract function getTitle();
		public abstract function setVolume($vol);
		public abstract function getVolumeLimits();
		public abstract function getAudioLength();
		public abstract function getAudioPos();
		public abstract function setAudioPos($pos);
	}

	class PlayerStatus
	{
		public function __construct($playing, $title, $volume, $paused, $audiopos, $audiolen)
		{
			$this -> playing = $playing;
			$this -> title = $title;
			$this -> volume = $volume;
			$this -> paused = $paused;
			$this -> audiopos = $audiopos;
			$this -> audiolen = $audiolen;
		}
	}

	class VolumeLimit
	{
		public function __construct($upper, $lower)
		{
			$this -> upper = $upper;
			$this -> lower = $lower;
		}
	}
?>