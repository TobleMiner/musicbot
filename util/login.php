<?php
	class AuthHandler
	{
		public function __construct()
		{
			require_once(__DIR__."/../config/mysql.php");
			require_once("random.php");
			$this -> mysqli = new mysqli(MysqlCredentials::$host, MysqlCredentials::$username, MysqlCredentials::$password, MysqlCredentials::$database, MysqlCredentials::$port);
		}

		public function createAccount($uname, $pass)
		{
			$table = MysqlStructure::$usertable;
			$salt = Random::getRandomString(15);
			$hash = hash("sha256", $pass.$salt);
			$userquery = $this -> mysqli -> prepare(sprintf("INSERT INTO %s(username, password, salt) VALUES (?, ?, ?)", $table));
			$userquery -> bind_param("sss", $uname, $hash, $salt);
			$userquery -> execute();
			$userquery -> close();
		}

		public function changePassword($userid, $pass)
		{
			$table = MysqlStructure::$usertable;
			$salt = Random::getRandomString(15);
			$hash = hash("sha256", $pass.$salt);
			$userquery = $this -> mysqli -> prepare(sprintf("UPDATE %s SET password=?, salt=? WHERE id=?", $table));
			$userquery -> bind_param("ssi", $hash, $salt, $userid);
			$userquery -> execute();
			$userquery -> close();
		}

		public function login($uname, $pass)
		{
			$table = MysqlStructure::$usertable;
			$userquery = $this -> mysqli -> prepare(sprintf("SELECT * FROM %s WHERE username=?", $table));
			$userquery -> bind_param("s", $uname);
			$userquery -> execute();
			$userid = FALSE;
			$passwd = null;
			$salt = null;
			$userquery -> bind_result($userid, $_, $passwd, $salt);
			if($userquery -> fetch())
			{
				if($passwd === hash("sha256", $pass.$salt))
				{
					$userquery -> close();
					return $userid;
				}
			}
			$userquery -> close();
			return FALSE;
		}

		public function createSession($userid, $uid, $validuntil)
		{
			$table = MysqlStructure::$sessiontable;
			$sessionquery = $this -> mysqli -> prepare(sprintf("INSERT INTO %s(userid, uniqueid, validuntil) VALUES (?, ?, ?)", $table));
			$sessionquery -> bind_param("iss", $userid, $uid, date("Y-m-d H:i:s", $validuntil));
			$sessionquery -> execute();
		}

		public function getSession($uid)
		{
			$table = MysqlStructure::$sessiontable;
			$sessionquery = $this -> mysqli -> prepare(sprintf("SELECT * FROM %s WHERE uniqueid=? AND validuntil>=?", $table));
			$sessionquery -> bind_param("ss", $uid, date("Y-m-d H:i:s"));
			$sessionquery -> execute();
			$userid = FALSE;
			$sessionquery -> bind_result($_, $userid, $_, $_);
			if($sessionquery -> fetch())
			{
				require_once("user.php");
				$sessionquery -> close();
				return new User($userid);
			}
			$sessionquery -> close();
			return FALSE;
		}

		public function deleteSession($uid)
		{
			$table = MysqlStructure::$sessiontable;
			$sessionquery = $this -> mysqli -> prepare(sprintf("DELETE FROM %s WHERE uniqueid=?", $table));
			$sessionquery -> bind_param("s", $uid);
			$sessionquery -> execute();
		}

		public function deleteSessions($userid)
		{
			$table = MysqlStructure::$sessiontable;
			$sessionquery = $this -> mysqli -> prepare(sprintf("DELETE FROM %s WHERE userid=?", $table));
			$sessionquery -> bind_param("i", $userid);
			$sessionquery -> execute();
		}
	}
?>