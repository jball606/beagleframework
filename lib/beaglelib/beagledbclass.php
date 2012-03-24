<?php
/**
 * The beagleDbClass is my way of standarizing the db setup
 * It is influnced by the Media Net Link db class but I don't use MBD2 so I had to do a complete re-write to use the base DB functons
 * You must create a model class that extends off this that will pass the proper variables
 * @author Jason Ball
 * @package Beagleframework
 * 
 */
class beagleDbClass extends beagleerrorbase
{
	
	protected $db = false;
	protected $auditfields = array();
	
	/**
	 * Table Name for model
	 * @var string
	 */
	protected $table = false;
	
	/**
	 * Does the table have standard auditing fields
	 * @example created_by, updadated_by, created_date, updated_date
	 * @var boolean
	 */
	protected $auditing = false;
	protected $sequence = false;
	protected $no_empty_string_columns = array(); // columns that should be converted to null if empty string is present
	
	/**
	 * Primary key of model
	 * @var string
	 */
	protected $pkey =false;
	private $dbtimestampformat = "Y-m-d H:i:s";
	
	/**
	 * array of fields to validate on
	 * @var array
	 * @see beagleDbClass::validate()
	 */
	protected $valid_fields = array();
	
	/**
	 * array of tables to join on
	 * @var array
	 */
	protected $join = false;
	
	public function __construct()
	{
		$this->loadDB();
	}
	
	/**
	 * This Method is Used to load DB variables
	 * @param resouce DB
	 * @return void
	 */
	protected function loadDB($db="")
	{
		if($this->table == false)
		{
			$this->table = get_called_class();
		}
		
		if($this->db == false)
		{
			if(is_resource($db))
			{
				$this->db = $db;
			}
			elseif(isset($GLOBALS['DB']))
			{
				$this->db = $GLOBALS['DB'];
			}
		}
		
		if($this->auditing == true && count($this->auditfields) == 0)
		{
			
			if(defined("__USERKEY__") && isset($_SESSION[__USERKEY__]))
			{
				$aperson = $_SESSION[__USERKEY__];
			}
			else 
			{
				$aperson = ucfirst(get_class($this)) . '::add';
			}
			
			if(defined("__CREATED__"))
			{
				$this->auditfields['C'][__CREATED__.'_by'] = $aperson;
				$this->auditfields['C'][__CREATED__.'_date'] = date($this->dbtimestampformat,time());
			}
			
			if(defined("__MODIFIED__"))
			{
				$this->auditfields['U'][__MODIFIED__.'_by'] = $aperson;
				$this->auditfields['U'][__MODIFIED__.'_date'] = date($this->dbtimestampformat,time());
			}
		}
			
		if($this->table == false)
		{
			print "Invalid Table";
			print $this->cleanBackTrace();
			exit;
		}
			
	}
	
	/**
	 * Method to find the preset in the model and pass them to any db action if needed
	 * @param array $want
	 * @param array $have
	 * @return array
	 */
	public function getPresets($want=array(),$have=array())
	{
		
		if(!is_array($want) || !is_array($have))
		{
			return $have;
		}
		
		if(count($want) == 0 || count($have) == 0)
		{
			return $have;
		}
		
		if(!is_array($this->valid_fields) || count($this->valid_fields) == 0)
		{
			return $have;
		}

		$set = array();
		
		foreach($this->valid_fields as $k => $i)
		{
			if(isset($i['preset']))
			{
				$set[$k] = $i['preset'];
			}
		}
		
		foreach($want as $k => $i)
		{
			if(!isset($have[$i]) || trim($have[$i]) == "")
			{
				if(isset($set[$i]))
				{
					$have[$i] = $set[$i];
				}
			}
		}
		
		return $have;
	}

	/**
	 * Mostly for dates but I am sure I will find more that we need
	 * 
	 * array('type'=>date,'output'=>'Y-m-d')
	 * mktime will return unix timestamp
	 * @param array $data
	 * @return array $formateddata
	 */
	public function formatData($data)
	{
		if(!is_array($this->valid_fields) || count($this->valid_fields) == 0)
		{
			return $data;
		}
		
		if(is_array($data) && count($data)>0)
		{
			$tmp = array();
			
			foreach($data as $k => $i)
			{
				if(isset($this->valid_fields[$k]))
				{
					$vf = $this->valid_fields[$k];
					if(isset($vf['type']))
					{
						if(strtolower($vf['type']) == 'date')
						{	
							if(isset($vf['output']))
							{
								if($i == "")
								{
									$tmp[$k] = "";
								}
								else if(strtolower($vf['output']) != 'mktime')
								{
									$tmp[$k] = date($vf['output'],strtotime($i));
								}
								else 
								{
									$tmp[$k] = strtotime($i);
								}
							}
							else 
							{
								$tmp[$k] = $i;
							}
							
						}
						else 
						{
							$tmp[$k] = $i;
						}
					}
					else 
					{
						$tmp[$k] = $i;
					}
				}
				else 
				{
					$tmp[$k] = $i;
				}
				
			}
			
		}
		return $tmp;
	}
	
	/**
	* Validate Class
	* 
	* Pass an array that you want to add or edit and this class will the protected fields varable and 
	* Do a backend validate for you.  Currently I am using the simple R822 for email validatetion,
	* but that is really outdated and something else will be needed.  I just want to try and make this 
	* self containted.
	* <pre>
	* examples
	*  $val = array('name'=>array('type'=>'varchar',
	*  					'size'=>255,
	*  					'null'=>false),
	*  			 'contact_email'=>array('type'=>'email',
	*  									 'null'=>false),
	*  			  'state'=>array('type'=>'integer',
	*  							 'size'=>2),
	*  			  'country'=>array('type'=>'varchar',
	*							   'size'=>2),
	*			  'testdate'=>array('type'=>'date'));
	*	</pre>
	* @author Jason Ball
	* 	
	*/
	public function validate($fields,$type="edit")
	{
		//Nothing To validate
		if(!is_array($this->valid_fields) || count($this->valid_fields) == 0)
		{
			return true;
		}
		
		//Not passed the right stuff
		if(!is_array($fields))
		{
			$this->storeError("Field list must be an array");
			return false;
		}
		
		foreach($this->valid_fields as $k => $i)
		{
			//If you pass it and it can't be null then you better have data
			if($type == "add")
			{
				//You have to have all the null fields
				if(isset($i['null']) && $i['null'] == false && (!isset($i['serial']) || $i['serial'] == false))
				{
					if(!isset($i['default']))
					{
						if(!isset($fields[$k]) || trim($fields[$k]) == "")
						{
							$this->storeError($k." is a required field, you did not pass it");
							return false;
						}
					}
				}
			}
			else
			{
				if(isset($i['null']) && $i['null'] == false)
				{
					if(isset($fields[$k]) && trim($fields[$k]) == "")
					{
						$this->storeError($k." is a required field, you did not pass it");
						return false;
					}
				}
			}

			if(isset($fields[$k]))
			{
				if(isset($i['type']))
				{
					if($i['type'] == "integer" || $i['type'] == 'float' || $i['type'] == "numeric")
					{
						if(trim($fields[$k]) == "")
						{
							$fields[$k] = $i['default'];
						}
						if(!is_numeric($fields[$k]))
						{
							$this->storeError($k." is a numeric field, you passed invalid data");
							return false;
						}
					}
					
					if($i['type'] == 'date')
					{
						if(!is_numeric($fields[$k]) && trim($fields[$k]) != "")
						{
							if(!is_numeric(strtotime($fields[$k])))
							{
								$this->storeError($k." is a date field, you passed invalid data");
								return false;
							}
						}
					}
					if($i['type'] == "email")
					{
						if(trim($fields[$k]) != "" && !$this->isValidEmail($fields[$k]))
						{
							$this->storeError($k." is an email field, you passed invalid data in the form of ".$fields[$k]);
							return false;
						}
					}
					
				}
				
				if(isset($i['size']))
				{
					if(strlen($fields[$k])>$i['size'])
					{
						$this->storeError($k."is of size ".$i['size'].", you passed invalid data");
						return false;
					}
				}
				
				
				
			}
			
		}
		return true;
	}

	
	/**
	 * Use to return an array of data where the primary key is the hash key
	 * @param $val mixed a primary key of a record to return or an any of key/value pairs used to generate a where clause
	 * @param $opts array of options that can be passed in for other process, 'count' returns a count integer. 'assoc' returns an associative array (getAssoc()) when not doing a search by primary key. 'getcol' returns one column as an array 
	 * @access public
	 * @return a hash or array of hashes depending on if you passed a primary key or key/value pairs
	 * @author Jason Ball
	 */
	public function getByKey($val = array(),$options = array())
	{
		$tmp = $this->get($val,$options);
		$t2 = array();
		
		if($this->pkey)
		{
			foreach($tmp as $i)
			{
				$t2[$i[$this->pkey]] = $i;
			}
			return $t2;
		}
		else
		{
			return $tmp;
		}
		return false;
	}
	
	/**
	 * Method used to validate Email
	 * @param string $email
	 */
	public function isValidEmail($email)
	{
		if(!$this->isValidEmailAddress($email, true))
		{
			return false;
		}
		else
		{
			return true;
		}
		
	} 
	
	
	/**
     * This is a email validating function that simply validates whether 
     * 
     * an email is of the common internet form: <user>@<domain>.
     * This can be sufficient for most people. 
     * Optional stricter mode restricts
     * mailbox characters allowed to alphanumeric, full stop, hyphen
     * and underscore.
     *
     * @param  string  $data   Address to check
     * @param  boolean $strict Optional stricter mode
     * @return mixed           False if it fails, an indexed array
     * @author      Richard Heyes <richard@phpguru.org>
 	 * @author      Chuck Hagenbuch <chuck@horde.org   
 	 * @copyright   2001-2010 Richard Heyes                
     */
    private function isValidEmailAddress($data, $strict = false)
    {
    	
    	$regex = $strict ? '/^([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})$/i' : '/^([*+!.&#$|\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})$/i';
        if (preg_match($regex, trim($data), $matches)) 
        {
        	return array($matches[1], $matches[2]);
        }
        else 
        {
            return false;
        }
    }
    
    /**
     * Get elements that are in the table 
     * 
     * @param array $array
     * @return mixed (array, false)
     * @author Jason Ball
     */
    public function getTableOnlyElements($array=array())
    {
    	$this->loadDb();
    	
    	if(isPopArray($array))
    	{
    		$SQL = "Select * from ".$this->table." limit 1";
    		$rows = $this->db->query($SQL)->getColumnNames();
    		
    		$tmp = array();
    		foreach($rows as $i)
    		{
    			if(array_key_exists($i, $array))
    			{
    				if(is_array($array[$i]))
    				{
    					$array[$i] = implode(",",$array[$i]);
    				}
    				
    				$tmp[$i] = $array[$i];
    			}
    		}
    		
    		return $tmp;
    	}
    	
    	return false;
    }
	
    /**
     * Get all the column names of a table.  Good for blank arrays
     * @return Array
     * @author Jason Ball
     */
    public function getTableColumnNames()
    {
    	$this->loadDB();
    	$SQL = "Select * from ".$this->table." limit 1";
    	return $this->db->query($SQL)->getColumnNames();	
    }
    
	/**
	 * Add Data to DB
	 * @param array $array (data)
	 * @param array $options (printsql)
	 * @return error or integer
	 * @author Jason Ball
	 * @copyright 2011-04-01
	 */
	public function add($array=array(),$options=array())
	{
		$this->loadDB();
		
		$fields = array();
		$values = array();
		if(is_array($array) && count($array)>0)
		{
		
			if(!$this->validate($array,'add'))
			{
				$this->storeError("Some Fields were not valid");
				return false;
			}

			//Take care of defaults
			foreach($array as $k => $i)
			{
				if(trim($i) == "")
				{
					if(isset($this->valid_fields[$k]['default']))
					{
						$array[$k] = $this->valid_fields[$k]['default'];
					}
					
				}
			}
			
			$array = $this->getTableOnlyElements($array);

			if($array == false)
			{
				$this->storeError("No valid array was passed");
				return false;
			}
			
			//Setup Auditing fields
			foreach($this->auditfields as $k => $i)
			{
				foreach($i as $vk => $v)
				{
					if(!isset($array[$vk]))
					{
						$array[$vk] = $v;
					}
				}
			}
			
			foreach($array as $k => $i)
			{
				if(!is_array($i))
				{
				 	$j = $this->escapeChar($i);
				 	if($j !== false)
				 	{
				 		$fields[] = $k;
				 		$values[] = $j;
				 		
				 	}
				} 	
			}
		}
		
		
		
		if(count($fields)>0 && count($values) >0 && count($fields) == count($values))
		{
			
			$value = $this->db->add($this->table, $fields,$values,$this->pkey);
			
			if(is_numeric($value) && method_exists($this, 'dataLog'))
			{
				$arr[$this->pkey] = $value;
				$this->dataLog($arr,$array,$action="add");
			}
			//MySQL version
			
			return $value;
			return true;
		}
		return false;
		 
	}
	
	/**
	 * This Method is used to update a record based on passed keys
	 * @param array $keys 			for the where clause array(field=>value, field=>value)
	 * @param array $values			for the update clause array(field=value, field=>value)
	 * @param boolian $printsql		for debug, will show the SQL clause
	 */
	public function update($keys=array(), $values = array(),$printsql=false)
	{
		$this->loadDB();
			
		if(!is_array($keys))
		{
			if(!$this->pkey)
			{
				print "Invalid update keys <br/>";
				print $this->cleanBackTrace();
				exit;
				return false;
			}
			$tmp[$this->pkey] = $keys;
			$keys = $tmp;
			
		}
		
		if(!is_array($values))
		{
			print "Invalid update Values <br/>";
			print $this->cleanBackTrace();
			exit;
			return false;
		}
		
		
		$keys = $this->getTableOnlyElements($keys);
		$values = $this->getTableOnlyElements($values);
		
		if($keys == false || $values == false)
		{
			$this->storeError("No valid keys or fields were passed");
			return false;
		}
		
		if(!$this->validate($values))
		{
			return false;
		}
		
		if(isset($this->auditfields['U']))
		{
			foreach($this->auditfields['U'] as $k => $i)
			{
				if(!isset($values[$k]))
				{
					$values[$k] = $i;
				}
			}
		}
		
		foreach($values as $k => $i)
		{
			if(!is_array($i))
			{
				if(trim($i) == "")
				{
					$values[$k] = null;
				}
			}
		
		}
		
		if(method_exists($this, 'dataLog'))
		{
			$this->dataLog($keys,$values,$action="update");
		}
		
		$keys = $this->getWhere($keys);
		$values = $this->getWhere($values);
		//Because is null won't work in update
		foreach($values as $k => $i)
		{
			if(strpos($i,'is null')!==false)
			{
				$values[$k] = str_replace('is null', '= null',$i);
			}
		}
		
		$SQL = "update ".$this->table." set ".implode(", ",$values)." where ".implode(" and ",$keys).";";
		
		if($printsql == true)
		{
			writeLog($SQL);
			printSQL($SQL);
		}
		
		return $this->db->query($SQL);	
		
		
	}
	
	/**
	 * Method to figure out if you need something added or updated and does so
	 * @param array $keys 			for the where clause array(field=>value, field=>value)
	 * @param array $values			for the update clause array(field=value, field=>value)
	 */
	public function addOrUpdate($keys,$values)
	{
		if(!is_array($values))
		{
			return false;
		}
		
		$test = $this->get($keys);
	
		if($test === false)
		{
			return $this->add($values);
		}
		else 
		{
			if($this->pkey)
			{
				foreach($test as $k => $i)
				{
					$this->update($i[$this->pkey],$values);
				}
			}
			else 
			{
				$this->update($keys,$values);
			}
			
			if($this->pkey && isset($test[0][$this->pkey]))
			{
				return $test[0][$this->pkey];
			}
		}
	}
	
	/**
	 * Use this function to get arrays of data from the DB
	 * @param array $keys (where items)
	 * @param array $options (single , orderby, printsql,fields,limit,join )
	 * <pre>
	 * single =>	Boolian		will only return a single record
	 * orderby => 	string		is for ordering your array
	 * pringsql => 	false		debugging, will print out SQL
	 * fields => 	array		fields you want
	 * limit =>		integer		limit result
	 * </pre>
	 * @return array
	 */
	public function get($keys=array(),$options = array())
	{
		
		$ops = defaultArgs($options, array('single'=>false,
											'orderby'=>FALSE,
											'printsql'=>false,
											'fields'=>false,
											'limit'=>false,
											'join'=>false,
										));
		
		$this->loadDB();
		
		$tmp = array();

		if(isPoparray($keys))
		{
			$tmp = $keys;
		}
		else 
		{
			if($this->pkey == false)
			{
				print("Invalid search <BR/>");
				print $this->cleanBackTrace();
				exit;
			}
			
			$tmp[$this->pkey] = $keys;
		
		}
		
		$junk = $this->getWhere($tmp);
		$fields = "*";
		if(isPopArray($ops['fields']))
		{
			$fields = implode(",",$ops['fields']);
		}
		
		$SQL = "SELECT $fields FROM ".$this->table;
		
		$join = array();
		if(isPopArray($this->join))
		{
			foreach($this->join as $i)
			{
				$join[] = $i;
			}
		}
		
		if(isPopArray($ops['join']))
		{
			foreach($ops['join'] as $i)
			{
				$join[] = $i;
			}
		}
		
		if(isPopArray($join))
		{
			foreach($join as $i)
			{
				$SQL .= " ".$i;
			}
		}
		
		if(isPopArray($junk))
		{
			$SQL .= " where ".implode(" and \n ",$junk);
		}
		
		
		if($ops['orderby'])
		{
			$SQL .= " order by ".$ops['orderby'];	
		}
		
		if(isSetNum($ops['limit']))
		{
			$SQL .= " limit ".$ops['limit'];
		}
		
		if($ops['printsql'])
		{
			writeLog($SQL);
			printSQL($SQL);
			print("<BR/>");
		}
	
		$result = $this->db->query($SQL);
		
//		if(!is_array($keys))
//		{
//			$row = $result->fetchRow();
//			return $row;
//		}
		
		$final = array();
		while($row = $result->fetchRow())
		{
			
			$final[] = $row;	
		
		}
		
		if(is_array($final) && count($final)>0)
		{
			return $final;
		}
		
		return false;
	}
	
	/**
	 * Method to return a single row and not put it in an enumerated array but just return the values into a key/value pair array
	 * @param array $keys 			for the where clause array(field=>value, field=>value)
	 * @param array $options
	 * @see beagleDbClass::get
	 */
	public function getOne($keys,$options=array())
	{
		$opt['single'] = true;
		if(isPopArray($options))
		{
			foreach($options as $k => $i)
			{
				$opt[$k] = $i;
			}
		}
		
		$row = $this->get($keys,$opt);
		if(is_array($row))
		{
			if(isset($row[0]))
			{
				return $row[0];
			}
		}
		else 
		{
			return $row;
		
		}
	}
	
	/**
	 * This Method is used to do a standard CRUD delete for a single table
	 * @param array $keys
	 * @return boolian
	 * @author Jason Ball
	 */
	public function delete($keys)
	{
		$this->loadDB();
		if(!is_array($keys))
		{
			if($this->pkey == false)
			{
				print("Invalid Values <br/>");
				print $this->cleanBackTrace();
				exit;
			}
			
			$tmp[$this->pkey] = $keys;
			$keys = $tmp;
		}
		
		
		$junk = $this->getWhere($keys);
		
		if(count($junk)>0)
		{
			if(method_exists($this, 'dataLog'))
			{
				$this->dataLog($keys,array(),'delete');
			}
		
			$SQL = "delete from ".$this->table." where ".implode(" and \n ",$junk);
			$this->db->query($SQL);
			return true;
		}
		
		return false;
		
	}
	
	/**
	 * 
	 * This version of get where requires you to pass an array of  [table][field]=>value
	 * @param array $array
	 * @return array
	 * @author Jason Ball
	 */
	public function getSearchWhere($array,$fieldonly=false)
	{
		$this->loadDB();
		
		foreach($array as $table => $fields)
		{	
			$prewhere[$table] = $this->getWhere($fields);
			
		}
		
		
		$tmp = array();
		foreach($prewhere as $k => $i)
		{
			foreach($i as $v)
			{	
				if($fieldonly == true)
				{
					$tmp[] = $v;
				}
				else 
				{
					$tmp[] = $k.".".$v;
				}
			}
		}
		
		return $tmp;
	}
	
	/**
	 * This method tries to create the where clause based on your array
	 * 
	 * @param array $array
	 * <pre>
	 * or => array(fieldname => array(one, two)
	 * database_field_name => element(name) so if you need to copy one field to another you can use this array system [database_field_name] = uesr
	 * array[item] = array(value, value) use this for "in" feature
	 * </pre>
	 * @return array
	 */
	private function getWhere($array=array())
	{
		$tmp = array();
		foreach($array as $k => $i)
		{
			if($this->db->checkKeyWord($k))
			{
				$k = $this->db->escapeKeyWordField($k,$this->table);
			}
			
			if($k == 'or')
			{
				$otmp = array();
				foreach($i as $fieldname => $v)
				{
					foreach($v as $line)
					{
						$otmp[] = $this->getWhere(array($fieldname=>$line));
					}
				}	
				$oftmp = array();
				foreach($otmp as $ori)
				{
					foreach($ori as $j)
					{
						$oftmp[] = $j;
					}
				}
				
				$tmp[] = "(".implode(" or ",$oftmp).")";
			}
			elseif(!is_array($i) && trim(strtolower($i)) == "null" || $i === null)
			{
				$tmp[] = $k." is null";
			}
			elseif(!is_array($i) && trim(strtolower($i)) == "is not null")
			{
				$tmp[] = $k." is not null";
			}
			else 
			{
				$j = $this->escapeChar($i);
				if($j !== false)
				{
					if(!is_array($j) && strpos(strtolower($j),"null") !== false || $j === null)
					{
						
						if(strpos($j,"!")!==false)
						{
							$tmp[] = $k." is not null"; 
						}
						else 
						{
							$tmp[] = $k." is null";
						}
					}
					else 
					{
						if(is_array($j))
						{
							if(isset($j['database_field_name']))
							{
								if(strpos($j['database_field_name'],'!=')!==false && strpos($j['database_field_name'],'!=') === 0)
								{
									$tmp[] = $k." != ".str_replace('!=', '', $j['database_field_name']);
								}
								else 
								{
									$tmp[] = $k." = ".$j['database_field_name']." ";
								}
							}
							elseif(isset($j['sub_query']))
							{
								$tmp[] = $k." ".$j['sub_query'];	
							}
							elseif(isset($j['start']) || isset($j['end']))
							{
								$rangefields = array();
								if(isset($j['start']))
								{
									$rangefields[][$k] = $j['start'];
								}
								if(isset($j['end']))
								{
									$rangefields[][$k] = $j['end'];
								}
								
								foreach($rangefields as $i)
								{
									$line = $this->getWhere($i);
									$tmp[] = $line[0];
								}

							}
							elseif(isPopArray($j))
							{
								$tmp[] = $k." in ('".implode("','",$j)."')";
							}
						}
						else if(strpos($j,"LIKE")!==false && (strpos($j,'LIKE') === 1 || strpos($j,'LIKE') === 0) && strpos($j,'%') !== false)
						{
							if(strpos($j,"'LIKE") !== false)
							{
								$j = "'".trim(substr($j,5,strlen($j)));
							}
							elseif(strpos($j,'"LIKE') !== false)
							{
								$j = "'".trim(substr($j,5,strlen($j)));
							}
							elseif(strpos($j,"LIKE") !== false)
							{
								$j = trim(substr($j,4,strlen($j)));
							}
							
							$tmp[] = $k ." LIKE ".$j;
						}
						else 
						{	
							if(strpos($j,"!=") !== false && strpos($j,'!=') === 1)
							{
								$j = trim(str_replace('!= ','',$j));
								$j = trim(str_replace('!=','',$j));
								$tmp[] = $k." != ".$j;
							}
							elseif(strpos($j,'<=') !== false && (strpos($j,'<=') === 1 || strpos($j,'<=') === 0))
							{
								$j = trim(str_replace('<= ','',$j));
								$j = trim(str_replace('<=','',$j));
								$tmp[] = $k." <= ".$j;
							}
							elseif(strpos($j,'>=') !== false && (strpos($j,'>=') === 1 || strpos($j,'>=') === 0))
							{
								$j = trim(str_replace('>= ','',$j));
								$j = trim(str_replace('>=','',$j));
								$tmp[] = $k." >= ".$j;
							}
							elseif(strpos($j,'<') !== false && (strpos($j,'<') === 1 || strpos($j,'<') === 0) && strpos($j,'>') === false)
							{
								$j = trim(str_replace('< ','',$j));
								$j = trim(str_replace('<','',$j));
								$tmp[] = $k." < ".$j;
							}
							elseif(strpos($j,'>') !== false && (strpos($j,'>') === 1 || strpos($j,'>') === 0))
							{
								$j = trim(str_replace('> ','',$j));
								$j = trim(str_replace('>','',$j));
								$tmp[] = $k." > ".$j;
							}
							else 
							{						
								$tmp[] = $k." = ".$j;
							}
						}
					}
				}
			}
		}
		return $tmp;
	}
	
	
	private function escapeChar($data)
	{
	
		if(is_array($data))
		{
			return $data;
		}
		/*
		if(is_numeric($data))
		{
			return $data;
		}
		*/
		elseif(trim($data) == "")
		{
			return 'null';
		}
		elseif($this->db->checkDBFunctions($data))
		{
			return $data;	
		}
		else 
		{
			
			return "'".$this->db->escape($data)."'";
		}
		
		return false;
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
?>