<?php
/**
 * Like MDB2 but much leaner
 * @author jball
 *
 */
class pgdb
{
	private $dbconn = false;
	private $error = false;
	private $conn = array();
	
	public function __construct($in_args = array())
	{
		$args = $this->defaultArgs($in_args, array('host'=>'localhost',
													'port'=>'5432',
													'dbname'=>false,
													'user'=>false,
													'password'=>false));	
		
		
		$this->conn = $args;
		
		$this->loadDB();
	}
	
		
	public function __sleep()
	{
		if(isset($this->dbconn))
		{
			unset($this->dbconn);
		}
		
		return array_keys(get_object_vars($this));
	}
	
	public function __wakeup()
	{
		$this->loadDB();
	}
	
	private function loadDB()
	{
		if($this->conn['dbname'] != false && $this->conn['user'] != false && $this->conn['password'] != false)
		{
			$string = "";
			foreach($this->conn as $k => $i)
			{
				$string .= $k."=".$i." ";
			}
			
			
			$this->dbconn = pg_connect($string);	
		}
	}
	
	/**
	 * Run a basic query and then return a result class if working or false if not
	 * @param string $SQL
	 * @return result class
	 */
	public function query($SQL="")
	{
		if(trim($SQL) != "")
		{
			$res = pg_query($this->dbconn,$SQL);
			if(is_resource($res))
			{
				return new pgresult($res);
			}
			print("Invalid SQL Statement \n");
			print $this->cleanBackTrace();
			exit;
		}
	}
	
	private function defaultArgs($in_args, $defs) 
	{
		if (!is_array($in_args)) print 'argDefaults called with non-array args';
		if (!is_array($defs)) print 'argDefaults called with non-array defs';
	
		$out_args = array();
	
		foreach ($defs as $k => $v)
		{
			if(is_array($in_args))
			{
				$out_args[$k] = array_key_exists($k, $in_args) ? $in_args[$k] : $defs[$k];
			}
		}
	
		return $out_args;
	}

	public function getOne($SQL)
	{
		$row = pg_fetch_row(pg_query($this->dbconn, $SQL));
		
		if(isset($row[0]) && $row[0] != "")
		{
			return $row[0];
		}

		return false;
	}
	
	public function getAll($SQL)
	{
		$tmp = array();
		
		if($SQL != "")
		{
			
			$result = pg_query($this->dbconn,$SQL);
			while($row = pg_fetch_assoc($result))
			{
				$tmp[] = $row;
			}
			
						
		}
		
		return $tmp;
	}
	
	public function update($table,$values,$keys)
	{
		pg_update($this->dbconn,$table,$values,$keys);
	}
	
	public function escape($item = "")
	{
		if(is_array($item))
		{
			$tmp = array();
			foreach($item as $k => $i)
			{
				$tmp[$k] = pg_escape_string($this->dbconn,$i);
			}
			
			return $tmp;
		}
		else 
		{
			return pg_escape_string($this->dbconn,$item);
		}
	}
	
	private function cleanBackTrace()
	{
		$error = '';
		foreach (debug_backtrace() as $i)
		{
	    	if (isset($i['file']) && $i['function'] != 'cleanBackTrace')
	    	{
	      		$error .= $i['function'].'() at '.$i['file'].' line '.$i['line'] . "\n";
	    	}
		}
	
		return $error;
		
	}
}

class pgresult
{
	private $result;
	
	public function __construct($result = false)
	{
		if(is_resource($result))
		{
			$this->result = $result;
		}
		else 
		{
			print "Invalid Rsource";
			exit;
		}
	}
	
	public function fetchRow()
	{
		return pg_fetch_assoc($this->result);
		
	}
	
	public function fetchArray()
	{
		return pg_fetch_array($this->result);	
	}
	
	public function getAll()
	{
		$tmp = array();	
		while($row = $this->fetchRow())
		{
			$tmp[] = $row;
		}
	}

	public function numRows()
	{
		return $count = pg_num_rows($this->result);
	}
}

?>