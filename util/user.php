<?php
	class User
	{
		public static function addUser($username, $password)
		{
			require_once("login.php");
			(new AuthHandler()) -> createAccount($username, $password);
		}

		public static function getAllUsers()
		{
			require_once(__DIR__."/../config/mysql.php");

			$users = Array();
			$table = MysqlStructure::$usertable;
			$mysqli = new mysqli(MysqlCredentials::$host, MysqlCredentials::$username, MysqlCredentials::$password, MysqlCredentials::$database, MysqlCredentials::$port);
			$userquery = $mysqli -> prepare(sprintf("SELECT * FROM %s", $table));
			$userquery -> execute();
			$userid = FALSE;
			$userquery -> bind_result($userid, $_, $_, $_);
			while($userquery -> fetch())
			{
				array_push($users, new User($userid));
			}
			$userquery -> close();
			return $users;
		}

		public function __construct($userid, $username = FALSE)
		{
			$this -> userid = $userid;
			require_once("permission.php");
			$this -> perms = new Permissions($userid);
			require_once("login.php");

			if(!$username)
			{
				require_once(__DIR__."/../config/mysql.php");

				$table = MysqlStructure::$usertable;
				$this -> mysqli = new mysqli(MysqlCredentials::$host, MysqlCredentials::$username, MysqlCredentials::$password, MysqlCredentials::$database, MysqlCredentials::$port);
				$userquery = $this -> mysqli -> prepare(sprintf("SELECT * FROM %s WHERE id=?", $table));
				$userquery -> bind_param("i", $userid);
				$userquery -> execute();
				$userquery -> bind_result($_, $username, $_, $_);
				$userquery -> fetch();
				$userquery -> close();		
			}

			$this -> username = $username;
		}

		public function changePassword($password)
		{
			(new AuthHandler()) -> changePassword($this -> userid, $password);
		}

		public function grantPermission($permid)
		{
			$this -> perms = $this -> perms -> grant($permid);
		}

		public function revokePermission($permid)
		{
			$this -> perms = $this -> perms -> revoke($permid);
		}

		public function delete()
		{
			$this -> revokePermission(0);
			$this -> revokePermission(1);
			require_once(__DIR__."/../config/mysql.php");
			$table = MysqlStructure::$usertable;
			if(!$this -> mysqli)
				$this -> mysqli = new mysqli(MysqlCredentials::$host, MysqlCredentials::$username, MysqlCredentials::$password, MysqlCredentials::$database, MysqlCredentials::$port);
			$permquery = $this -> mysqli -> prepare(sprintf("DELETE FROM %s WHERE id=?", $table));
			$permquery -> bind_param("i", $this -> userid);
			$permquery -> execute();			
			$permquery -> close();			
		}
	}
?>