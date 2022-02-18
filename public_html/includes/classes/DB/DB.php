<?php
						
class DB 
{
	protected static $_instance = null;
	
	private $conn_id = false;
	private $sql_query = null;
	private $sql_result = null;
	private $conn_errors = 0;
	private $max_reconnect = 5;
	
	public static function Instance()
	{
		if (!isset($GLOBALS['DB']))
			$GLOBALS['DB'] = new self();
		return $GLOBALS['DB'];
	}
	
	public function connect() 
	{
		$this->conn_id = @mysql_connect(SQL_HOST, SQL_USER, SQL_PASS,1);
		if (!mysql_select_db(SQL_DB,$this->conn_id))
			trigger_error('mysql_select_db:' . mysql_error($this->conn_id), E_USER_WARNING);
		
		$version = $this->version();
		if($version >= 4.1)
		{
			$encoding = "utf8";
			if($encoding)
			{
				$sql_query = "SET NAMES '".$encoding."'";
				$this->execute($sql_query);
			}
		}
	}
	
	public function disconnect()
	{
		if($this->conn_id)
			mysql_close($this->conn_id);
		$this->conn_id = false;
	}
	
	private function version()
	{
		$version = $this->single("SELECT VERSION()");
		$version = preg_replace('/[^0-9|\.]/','',$version);
		return $version;
	}
	
	private function prepare($values)
	{
		if (is_array($values))
		{
			foreach ($values as $key=>$value)
			{
				$isExp = (strpos(strtolower($value), 'exp|') === 0);
				$values[$key] = ($value === null) ? null : (($isExp) ? $value : addslashes($value));
			}
		}
		else
			$values = ($values === null) ? null : addslashes($values);
		
		return $values;
	}
	
	public function exec_query($info, $table, $where = '', $replace = false, $on_duplicate_key = '')
	{
		$info = $this->prepare($info);
		// update
		if (!empty($where))
		{
			$fields = '';
			foreach ($info as $column=>$value)
			{
				$fix_value = $value;
				if ($value === null || strtolower($value) == 'null')
					$fix_value = 'null';
				else if (strtolower($value) == 'now()')
					$fix_value = 'now()';
				else if (strpos(strtolower($value), 'exp|') === 0)
					$fix_value = preg_replace('/^exp\|/i', '', $value);
				else
					$fix_value = "'$value'";
				
				$fields .= (strlen($fields)) ? ", `$column` = $fix_value" : " `$column` = $fix_value";
			}
			
			$sql_query = "UPDATE $table SET $fields WHERE $where";
			return $this->update($sql_query);
		}
		//insert or replace
		else
		{
			$columns = "";
			$values = "";
			
			foreach ($info as $column => $value)
			{
				$columns .= (strlen($columns)) ? ", `$column`" : " `$column`";
				
				$fix_value = $value;
				if ($value === null || strtolower($value) == 'null')
					$fix_value = 'null';
				else if (strtolower($value) == 'now()')
					$fix_value = 'now()';
				else if (strpos(strtolower($value), 'exp|') === 0)
					$fix_value = preg_replace('/^exp\|/i', '', $value);
				else
					$fix_value = "'$value'";
				
				$values .= (strlen($values)) ? ", $fix_value" : " $fix_value";
			}
			
			$sql_query = ($replace)
				? "REPLACE INTO $table ($columns) VALUES ($values) "
				: "INSERT INTO $table ($columns) VALUES ($values) ";
			if ($on_duplicate_key != '')
			{
				$sql_query .= " on duplicate key $on_duplicate_key";
			}
			return $this->insert($sql_query);
		}
	}
		
	public function execute($sql_query) 
	{
		if(!$this->conn_id)
			$this->connect();
		$BenchmarkStart = microtime(1);
		
		ErrorHandler::SetIgnore('db','MySQL server has gone away');
		$this->sql_result = mysql_query($sql_query, $this->conn_id);
		$dberr = mysql_errno($this->conn_id);
		if ($dberr == 2006)
		{
			ErrorHandler::RemoveIgnore('db');
			$this->conn_errors++;
			if ($this->conn_errors < $this->max_reconnect)
			{
				trigger_error("MySql server has gone away: Reconnecting...", E_USER_WARNING);
				$this->connect();
				return $this->execute($sql_query);
			}
			else
			{
				trigger_error("Maximum failed connection count {$this->max_reconnect} reached. Giving up.", E_USER_WARNING);
				return null;
			}
		}
		
		$BenchmarkEnd = microtime(1);
		ErrorHandler::RemoveIgnore('db');
		
		Sys::SetBenchMarkTime(($BenchmarkEnd - $BenchmarkStart));
		Sys::Log(preg_replace('/[\t ]+/', " ", $sql_query), 'db');
		
		if(!$this->sql_result && !preg_match('/^LOCK TABLES/',$sql_query) && $sql_query!="UNLOCK TABLES" || mysql_errno($this->conn_id) > 0)
			trigger_error($sql_query.":\n".mysql_error($this->conn_id), E_USER_WARNING);
		
		return $this->sql_result;
	}

	public function insert($sql_query)
	{
		if((!$this->sql_result)||($sql_query != $this->sql_query))
		{
			$this->execute($sql_query);
		}
		$this->clean();
		return mysql_insert_id($this->conn_id);        
	}
	
	public function update($sql_query)
	{
		if((!$this->sql_result)||($sql_query != $this->sql_query))
		{
			$this->execute($sql_query);
		}
		
		$this->clean();
		return mysql_affected_rows($this->conn_id);        
	}
	
	private function fetch($sql_query)
	{
		if((!$this->sql_result)||($sql_query != $this->sql_query))
			$this->execute($sql_query);
		
		$this->sql_query = $sql_query;
		$result = @mysql_fetch_object($this->sql_result);
		if(!$result)
			$this->clean();
		
		return $result;
	}
	
	public function row($sql_query, $self = false)
	{
		if (!$self)
			$this->clean();
		
		if( !$this->sql_result || $sql_query != $this->sql_query )
		{
			$this->execute($sql_query);
		}
		
		$this->sql_query = $sql_query;
		$result = @mysql_fetch_assoc($this->sql_result);
		if(!$result)
			$this->clean();
		
		return $result;
	}
	
	public function result($sql_query)
	{
		$ret_array = array();   	
		
		while($this->res = $this->row($sql_query,true))
		{
			$ret_array[] = $this->res;
		}
		
		if ($this->sql_result)
			@mysql_free_result($this->sql_result);
		$this->clean();
		return $ret_array;
	}
	
	public function single($sql_query)
	{
		$this->execute($sql_query);
		$this->clean();
		if (mysql_num_rows($this->sql_result))
			return @mysql_result($this->sql_result,0);
		return '';
	}
	
	public function num_rows($sql_query) 
	{
		$this->execute($sql_query);
		$this->clean();
		return @mysql_num_rows($this->sql_result);
	}
	
	public function lock($table)
	{
		$sql_query="LOCK TABLES `".$table."` WRITE";
		$this->execute($sql_query);
	}
	
	public function unlock()
	{
		$sql_query="UNLOCK TABLES";
		$this->execute($sql_query);
	}
	
	public function begin() 
	{
		return $this->execute('BEGIN');
	}

	public function commit() 
	{
		return $this->execute('COMMIT');
	}

	public function rollback() 
	{
		return $this->execute('ROLLBACK');
	}
	
	public function clean()
	{
		$this->sql_query = "";
	}
	
	public function db_null(&$value)
	{
		$value = ($value === null || strtolower($value) === 'null' || (strlen($value) == 0)) ? 'null' : $value;
	}
	
	public function is_null($value)
	{
		return ($value === null || strtolower($value) === 'null');
	}
	
	// database timezone settings can be different
	// we use system timesettings
	public function now()
	{
		return date('Y-m-d H:i:s');
	}
}

$GLOBALS['DB'] = new DB;

