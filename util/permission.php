<?php
	class Permissions
	{
		public static $permIdAdmin = 0;
		public static $permIdControl = 1;

		public function __construct($userid)
		{
			require_once(__DIR__."/../config/mysql.php");

			$this -> userid = $userid;

			$this -> canAdmin = FALSE;
			$this -> canControl = FALSE;

			$this -> mysqli = new mysqli(MysqlCredentials::$host, MysqlCredentials::$username, MysqlCredentials::$password, MysqlCredentials::$database, MysqlCredentials::$port);
			$table = MysqlStructure::$permtable;
			$permquery = $this -> mysqli -> prepare(sprintf("SELECT * FROM %s WHERE userid=?", $table));
			$permquery -> bind_param("i", $userid);
			$permquery -> execute();
			$permid = FALSE;
			$_ = null;
			$permquery -> bind_result($_, $_, $permid);
			while($permquery -> fetch())
			{
				if($permid === 0)
				{
					$this -> canAdmin = TRUE;
					$this -> canControl = TRUE;
				}
				elseif($permid === 1)
					$this -> canControl = TRUE;
			}
			$permquery -> close();
		}

		public function grant($permid)
		{
			$table = MysqlStructure::$permtable;
			$permquery = $this -> mysqli -> prepare(sprintf("INSERT INTO %s (userid, permissionid) VALUES (?, ?)", $table));
			$permquery -> bind_param("ii", $this -> userid, $permid);
			$permquery -> execute();
			$permquery -> close();			
			return new Permissions($this -> userid);
		}

		public function revoke($permid)
		{
			$table = MysqlStructure::$permtable;
			$permquery = $this -> mysqli -> prepare(sprintf("DELETE FROM %s WHERE userid=? AND permissionid=?", $table));
			$permquery -> bind_param("ii", $this -> userid, $permid);
			$permquery -> execute();			
			$permquery -> close();			
			return new Permissions($this -> userid);
		}

		public function has($permid)
		{
			if($permid === NULL || $permid === FALSE)
				return TRUE;
			if($permid == Permissions::$permIdControl)
				return $this -> canControl;
			if($permid == Permissions::$permIdAdmin)
				return $this -> canAdmin;
			return NULL;
		}
	}
?>