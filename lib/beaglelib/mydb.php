<?php
/**
 * Like MDB2 but much leaner
 * @author Jason Ball
 * @copyright 03/01/2011
 \* @package Beagleframework
 * @subpackage Database Classes
 *
 */
class mydb
{
	private $dbconn = false;
	private $error = false;
	private $conn = array();
	const dbtype = "mysql";
	private $keywords = array('table'=>'table','action'=>'action','key'=>'key','archive'=>'archive','default'=>'default');
	private $transaction = false;
	 
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
			return " limit ".$limit." offset ".$offset;
		}
	}
	
	/**
	 * This Function is needed due to differences in DB return function
	 * @param string $key
	 * @return string
	 * @author Jason Ball
	 * @copyright 2011-08-02
	 */
	public function getInsertId()
	{
		return mysql_insert_id($this->dbconn);
	}
	
	public function getDbType()
	{
		return self::dbtype;
	}
	
	private function loadDB()
	{
		if($this->conn['dbname'] != false && $this->conn['user'] != false && $this->conn['password'] != false)
		{
			$this->dbconn = mysql_connect($this->conn['host'],$this->conn['user'],$this->conn['password'],true);
			if($this->dbconn == false)
			{
				print("Can not connect to server ");
				exit;
			}
		
			$check = mysql_select_db($this->conn['dbname'],$this->dbconn);
			if($check == false)
			{
				print("Can't find database");
				exit;
			}
				
		}
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
	
	/**
	 * Run a basic query and then return a result class if working or false if not
	 * @param string $SQL
	 * @return result class
	 */
	public function query($SQL="")
	{
		if(trim($SQL) != "")
		{
			$res = mysql_query($SQL,$this->dbconn);
			if(is_resource($res))
			{
				return new myresult($res);
			}
			if($res == true)
			{
				return true;
			}
			
			if($this->transaction)
			{
				$this->rollback();
			}
			
			print("Invalid SQL Statement <br/>\n");
			printSQL($SQL."<BR>");
			print $this->cleanBackTrace();
			exit;
		}
	}
	
	/**
	 * Have to have this method because PGSQL and MYSQL are different
	 */
	public function navNoLetter()
	{
		return " not REGEXP '[A-Z]' ";
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
		$row = mysql_fetch_row(mysql_query($SQL,$this->dbconn));
		
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
		return "upper(".$table.".".$field.") like upper('".$this->escape(trim($value))."') and ";	
	}
	
	public function getAll($SQL)
	{
		$tmp = array();
		
		if($SQL != "")
		{
			
			$result = mysql_query($SQL,$this->dbconn);
			while($row = mysql_fetch_assoc($result))
			{
				$tmp[] = $row;
			}
			
						
		}
		
		return $tmp;
	}
	
	public function update($table,$values,$keys,$printsql=false)
	{
		$SQL = " update ".$table." set ";
		$tmp = array();
		foreach($values as $k => $i)
		{
			if($this->checkKeyWord($k))
			{
				$k = $table.".".$k;
			}
			
			if($i == null)
			{
				$tmp[] = $k." = null ";
			}
			else 
			{
				if(isPopArray($i))
				{
					if(isset($i['database_field_name']))
					{
						$tmp[] = $k." = ".$i['database_field_name']." ";
					}
				}
				else 
				{
					$tmp[] = $k." = '".$this->escape($i)."'";
				}
			}
		}
		$SQL .= implode(",",$tmp);
		
		if(isPopArray($keys))
		{
			$SQL .= " where ";
			$tmp = array();
			foreach($keys as $k => $i)
			{
				if(is_array($i))
				{
					$j = array();
					foreach($i as $v)
					{
						$j[] = $this->escape($v);
					}
					
					if(isset($i['database_field_name']))
					{
						$tmp[] = $k." = ".$i['database_field_name']." ";
					}
					else 
					{
						$tmp[] = $k." in ('".implode("','",$j)."') ";
					}
					//$tmp[] = " ".$k." in ('".implode("','",$j)."');";
				}
				else 
				{
					$tmp[] = " ".$k." = '".$this->escape($i)."'";
				}
			}
			$SQL .= implode("and",$tmp);
		}
		
		if($printsql == true)
		{
			printSQL($SQL);
		}
		mysql_query($SQL,$this->dbconn);
			
	}
	
	public function escape($item = "")
	{
		if(is_array($item))
		{
			$tmp = array();
			foreach($item as $k => $i)
			{
				$tmp[$k] = mysql_real_escape_string($i);
			}
			
			return $tmp;
		}
		else 
		{
			return mysql_real_escape_string($item);
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
}

class myresult
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
		return mysql_fetch_assoc($this->result);
		
	}
	
	public function fetchArray()
	{
		return mysql_fetch_array($this->result);	
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
		return $count = mysql_num_rows($this->result);
	}
}

?>