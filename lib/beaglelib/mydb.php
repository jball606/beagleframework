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
	private $functions = array(
	//Math
	'ABS','ACOS','ACOS','ASIN','ATAN2','ATAN','CEIL','CEILING','CONV','COS','COT','CRC32','DEGREES','DIV','EXP','FLOOR','LN','LOG10','LOG2','LOG',
	'MOD','OCT','PI','POW','POWER','RADIANS','RAND','ROUND','SIGN','SIN','SQRT','TAN','TRUNCATE',
	//string
	'ASCII','BIN','BIT_LENGTH','CHAR_LENGTH','CHAR','CHARACTER_LENGTH','CONCAT_WS','CONCAT','ELT','EXPORT_SET',
	'FIELD','FIND_IN_SET','FORMAT','HEX','INSERT','INSTR','LCASE','LEFT','LENGTH','LOAD_FILE','LOCATE',
	'LOWER','LPAD','LTRIM','MAKE_SET','MATCH','MID','OCTET_LENGTH','ORD','POSITION','QUOTE','REPEAT','REPLACE',
	'REVERSE','RIGHT','RPAD','RTRIM','SOUNDEX','SPACE','STRCMP','SUBSTR','SUBSTRING_INDEX,SUBSTRING','TRIM','UCASE','UNHEX','UPPER',
	//Date
	'ADDDATE','ADDTIME','CONVERT_TZ','CURDATE','CURRENT_DATE','CURRENT_TIME','CURRENT_TIMESTAMP','CURTIME','DATE_ADD',
	'DATE_FORMAT','DATE_SUB','DATE','DATEDIFF','DAY','DAYNAME','DAYOFMONTH','DAYOFWEEK','DAYOFYEAR','EXTRACT','FROM_DAYS', 
	'FROM_UNIXTIME','GET_FORMAT','HOUR','LOCALTIME','LOCALTIMESTAMP','MAKEDATE','MAKETIME','MICROSECOND','MINUTE', 
	'MONTH','MONTHNAME','NOW','PERIOD_ADD','PERIOD_DIFF','QUARTER','SEC_TO_TIME','SECOND','STR_TO_DATE','SUBDATE',
	'SUBTIME','SYSDATE','TIME_FORMAT','TIME_TO_SEC','TIME','TIMEDIFF','TIMESTAMP','TIMESTAMPADD','TIMESTAMPDIFF','TO_DAYS',
	'UNIX_TIMESTAMP','UTC_DATE','UTC_TIME','UTC_TIMESTAMP','WEEK','WEEKDAY','WEEKOFYEAR','YEAR','YEARWEEK'
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
	
	public function escapeKeyWordField($field,$table)
	{
		return $table.'.'.$field;
	}
	
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
				print("<BR>".mysql_error());
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
	 * Query Wrapper
	 * 
	 * @param string $SQL
	 */
	public function execute($SQL="")
	{
		return $this->query($SQL);
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
			
			writeLog("Invalid SQL Statement \n");
			writeLog($SQL);
			writeLog(br2nl($this->cleanBackTrace()));
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
			$result = $this->query($SQL);
			
			while($row = $result->fetchRow())
			{
				$tmp[] = $row;
			}
			
						
		}
		
		return $tmp;
	}
	
	/**
	 * 
	 * Use this method to add records to a table, 
	 * @param string $table
	 * @param array $fields
	 * @param array $values
	 * @param boolean $has_pkey
	 * @return integer / void
	 * @author Jason Ball
	 */
	public function add($table,$fields,$values,$has_pkey=false)
	{
		foreach ($fields as $k => $i)
		{
			if($this->checkKeyWord($i))
			{
				$fields[$k] = $table.".".$i;
			}	
			
		}
		
		$SQL = "insert into ".$table." (".implode(",",$fields).") values (".implode(",",$values).") ";	
		
		$result = $this->query($SQL);
		
		if($has_pkey == true)
		{
			return $this->getInsertId();
		}
		
		return true;
	}
	
	/**
	 * 
	 * Used for updating, now this should not be used since the model DB system does the SQL statment
	 * @param string $table
	 * @param array $values
	 * @param array $keys
	 * @param boolean $printsql
	 * @deprecated
	 */
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
				elseif(strpos($i,'(') !== false && strpos($i,')') !== false)
				{
					$tmp[] = $k." = ".$i;
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

	public function getConnectionCriteria()
	{
		return $this->conn;
		
	}
	
	public function importSQLFile($filename)
	{
		$cmd = "mysql -h".$this->conn['host']." -u".$this->conn['user']." -p".$this->conn['password']." ".$this->conn['dbname'];
		$cmd .= " < ".$filename;
		exec($cmd,$output);
		if(isPopArray($output))
		{
			print_r2($output);
			writeLog($output);
			return false;
		}

		return true;
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
	
	/**
	 * Return all records in the form of an associative array
	 * 
	 */
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
		$x = mysql_num_fields($this->result);
		$tmp = array();
		for($a=0;$a<$x;$a++)
		{
			$tmp[] = mysql_field_name($this->result,$a);
		}
		return $tmp;
	}
	
	public function numRows()
	{
		return $count = mysql_num_rows($this->result);
	}
}

?>