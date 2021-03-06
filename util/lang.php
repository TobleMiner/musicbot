<?php
	require_once( __DIR__ . '/../config/lang.php' );

	class Langdict
	{
		/**
		 * @var Langdict
		 */
		public static $instance = NULL;

		private $dict;

		public function __construct()
		{
			$this->dict = Array();
		}

		public function addFile($file)
		{
			$lang = parse_ini_file($file);
			if ($lang === FALSE)
			{
				throw new Exception( "Failed parsing langfile '$file'" );
			}

			$this->dict = array_merge($this->dict, $lang);
		}

		public function get($key)
		{
			if (array_key_exists($key, $this->dict))
			{
				return $this->dict[$key];
			}

			return $key;
		}
	}

	function trans($key)
	{
		return Langdict::$instance->get($key);
	}

	Langdict::$instance = new Langdict();
	Langdict::$instance->addFile("lang/main/" . LangConf::$langfile);
?>
