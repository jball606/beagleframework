<?php
/**
 * Like MDB2 but much leaner
 * @author Jason Ball
 * @copyright 03/01/2011
 \* @package Beagleframework
 * @subpackage Database Classes
 *
 */
class pgdb
{
	private $dbconn = false;
	private $error = false;
	private $conn = array();
	private $keywords = array('table'=>'table','action'=>'action','key'=>'key','default'=>'default','value'=>'value');
	const dbtype = "pgsql";
	private $functions = array(
	//String	
	'SUBSTRING','TRIM','UPPER','LOWER','UPPER','STRPOS','SUBSTR','RTRIM','CONCAT','ENCODE','DECODE',
	
	//MATH
	'ACOS','ASIN','ATAN','ATAN2','COS','COT','SIN','TAN','ROUND','PI','POWER','RANDOM','FLOOR','CEIL','EXP','LOG','ABS','MOD',
	
	//TIME
	'AGE','CURRENT_DATE','CURRENT_TIME','CURRENT_TIMESTAMP','DATE_PART','DATE_TRUNC','EXTRACT','ISFINITE','JUSTIFY_HOURS','LOCALTIME','NOW',
	'LOCALTIMESTAMP','TIMEOFDAY',
	);
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
		//	unset($this->dbconn);
		}
		if(isset($this->db))
		{
			unset($this->db);
		}
		return array_keys(get_object_vars($this));
	}

	public function __wakeup()
	{
		$this->loadDB();
	}
	
/**
	 * Because mySQL has bad keyword management
	 *
	 * @param string $word
	 * @return boolean
	 */
	public function checkKeyWord($word)
	{
		if(isset($this->keywords[strtolower(trim($word))]))
		{
			return true;
		}
	}
	
	public function escapeKeyWordField($field,$table)
	{
		return '"'.$field.'"';
	}

	/**
	 * Is the DB string have a mysql function in it?  If so don't escape it
	 * 
	 * @param string $word
	 * @return boolean
	 * @author Jason Ball
	 */
	public function checkDBFunctions($word="")
	{
		foreach($this->functions as $k => $i)
		{
			if(strpos(strtoupper($word),$i."(")!== false && isHTMLString($word) == false)
			{
				return true;
			}
		}	
		
		return false;
	}
	
	/**
	 * This function is needed because each DB does this differently
	 * @param integer $limit
	 * @param integer $offset
	 * @return SQL String
	 * @author Jason Ball
	 * @copyright 2011-08-01
	 */
	public function limitOffset($limit=0,$offset=0)
	{
		if(is_numeric($limit) && is_numeric($offset))
		{
			return " offset ".$offset." limit ".$limit;
		}
	}
	
	/**
	 * This Function is needed due to differences in DB return function
	 * @param string $key
	 * @return string
	 * @author Jason Ball
	 * @copyright 2011-08-02
	 */
	public function getInsertId($key)
	{
		return " returning ".$key;
	}
	
	public function getDBType()
	{
		return self::dbtype;
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
	public function query($SQL="",$continue_on_failure=false)
	{
		if(trim($SQL) != "")
		{
			$res = pg_query($this->dbconn,$SQL);
			if(is_resource($res))
			{
				return new pgresult($res);
			}
			print("Invalid SQL Statement <BR/>");
			printSQL($SQL."<BR/>");
			print $this->cleanBackTrace();
				
			writeLog("Invalid SQL Statement \n");
			writeLog($SQL);
			writeLog(br2nl($this->cleanBackTrace()));
			if($continue_on_failure == false)
			{
				exit;
			}
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

	/**
	 * Query Wrapper
	 * 
	 * @param string $SQL
	 */
	public function execute($SQL="")
	{
		return $this->query($SQL);
	}
	
	/**
	 * Used for starting transactions
	 * 
	 */
	public function begin()
	{
		$this->query("BEGIN");
		$this->transaction = true;	
	}
	
	/**
	 * Used for rolling back transactions
	 * 
	 */
	public function rollback()
	{
		$this->query("ROLLBACK");
		$this->transaction = false;	
	}
	
	/**
	 * Used to commit transactions
	 * 
	 */
	public function commit()
	{
		$this->query("COMMIT");
		$this->transaction = false;
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
	
	/**
	 * Needed becuase mysql and pgsql are different
	 * Enter description here ...
	 * @param unknown_type $table
	 * @param unknown_type $field
	 * @param unknown_type $value
	 * @return string
	 * @author Jason Ball
	 * @copyright 8/14/2011
	 */
	public function getDbWhere($table,$field,$value)
	{
		return "upper(cast(".$table.".".$field." as text)) like upper(cast('".$this->escape(trim($value))."' as text)) and ";	
	}
	
	public function getAll($SQL)
	{
		$tmp = array();
		
		if($SQL != "")
		{
			
			$result = $this->query($SQL);
			
			while($row = $result->fetchRow())
			{
				$tmp[] = $row;
			}
			
						
		}
		
		return $tmp;
	}
	
	/**
	 * Have to have this method because PGSQL and MYSQL are different
	 */
	public function navNoLetter()
	{
		return " !~'[A-Z]'  ";
	}
	
	/**
	 * 
	 * Use this method to add records to a table, 
	 * @param string $table
	 * @param array $fields
	 * @param array $values
	 * @param string/boolen $has_pkey
	 * @return integer / void
	 * @author Jason Ball
	 */
	public function add($table,$fields,$values,$has_pkey=false)
	{
		foreach ($fields as $k => $i)
		{
			if($this->checkKeyWord($k))
			{
				$fields[$k] = $table.".".$i;
			}	
			
		}
		
		foreach($values as $k => $i)
		{
			if(!is_numeric($i) && substr($i,0,1) != "'" && $i != null && $i != 'null')
			{
				$values[$k] = "'".$i."'";
			}
		}
		
		$SQL = "insert into ".$table." (".implode(",",$fields).") values (".implode(",",$values).") ";	
		if($has_pkey !== false)
		{
			$SQL .= $this->getInsertId($has_pkey);
		}
		
		$result = $this->query($SQL);
		$tmp = $result->fetchArray();
		if(isset($tmp[0]))
		{
			return $tmp[0];
		}
		
		return true;
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
	
	/**
 	* Method to return a nice clean backtrace of data
 	* @author Brad Dutton
 	* @author Jason Ball
 	*/
	private function cleanBackTrace()
	{
		$return = "\n";
		$error = '';
		if(!is_cli())
		{
			$return = "<br/>";
			$error = "<br/>";
		}
		
		
		foreach (debug_backtrace() as $i)
		{
	    	if (isset($i['file']) && $i['function'] != 'cleanBackTrace')
	    	{
	      		$error .= $i['function'].'() at '.$i['file'].' line '.$i['line'] . $return;
	    	}
		}
	
		return $error;
	}

	public function getConnectionCriteria()
	{
		return $this->conn;
		
	}
	
	public function importSQLFile($filename)
	{
		$cmd = "PGPASSWORD=".$this->conn['password']." psql -U".$this->conn['user']." -h".$this->conn['host']." ".$this->conn['dbname'];
		$cmd .= " < ".$filename;
		exec($cmd,$output);
		if(isPopArray($output))
		{
			print_r2($output);
			writeLog($output);
			return true;
		}

		return true;
	}

	public function doesTableExist($table)
	{
		return $this->getOne("select table_name from information_schema.tables where table_name='".$table."' and table_catalog = '".$this->conn['dbname']."';");
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
		
		return $tmp;
	}

/**
	 * If you need field names, then you do this
	 * @return array 
	 * @author Jason Ball
	 */
	public function getColumnNames()
	{
		$x = pg_num_fields($this->result);
		$tmp = array();
		for($a=0;$a<$x;$a++)
		{
			$tmp[] = pg_field_name($this->result,$a);
		}
		return $tmp;
	}
	
	public function numRows()
	{
		return $count = pg_num_rows($this->result);
	}
}

?>