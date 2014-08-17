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
			$cmds['isplaying'] = new Command('isplaying', TRUE, FALSE, array($this, 'isPlaying'), 'isplaying');
			$cmds['play'] = new Command('play', TRUE, Permissions::$permIdControl, array($this, 'play'), 'play', new CmdArg('URI', 'uri'));
			$cmds['prev'] = new Command('prev', TRUE, Permissions::$permIdControl , array($this, 'prev'), 'prev');
			$cmds['next'] = new Command('next', TRUE, Permissions::$permIdControl , array($this, 'next'), 'next');
			$cmds['stop'] = new Command('stop', TRUE, Permissions::$permIdControl, array($this, 'stop'), 'stop');
			$cmds['pause'] = new Command('pause', TRUE, Permissions::$permIdControl , array($this, 'pause'), 'pause');
			$cmds['ispaused'] = new Command('ispaused', TRUE, FALSE, array($this, 'isPaused'), 'ispaused');
			$cmds['getstatus'] = new Command('getstatus', TRUE, FALSE, array($this, 'getStatus'), 'getstatus');
			$cmds['getvolume'] = new Command('getvolume', TRUE, FALSE, array($this, 'getVolume'), 'getvolume');
			$cmds['gettitle'] = new Command('gettitle', TRUE, FALSE, array($this, 'getTitle'), 'gettitle');
			$cmds['setvolume'] = new Command('setvolume', TRUE, Permissions::$permIdControl, array($this, 'setVolume'), 'setvolume', new CmdArg('volume', 'volume'));
			$cmds['getvolumelimits'] = new Command('getvolumelimits', TRUE, FALSE, array($this, 'getVolumeLimits'), 'getvolumelimits');
			$cmds['getaudiolength'] = new Command('getaudiolength', TRUE, FALSE, array($this, 'getAudioLength'), 'getaudiolength');
			$cmds['getaudiopos'] = new Command('getaudiopos', TRUE, FALSE, array($this, 'getAudioPos'), 'getaudiopos');
			$cmds['setaudiopos'] = new Command('setaudiopos', TRUE, Permissions::$permIdControl, array($this, 'setAudioPos'), 'setaudiopos', new CmdArg('pos', 'pos'));
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