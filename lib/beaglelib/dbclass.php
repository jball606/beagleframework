<?php
/**
 * The Dbclass is my way of standarizing the db setup
 * It is influnced by the MNL db class but I don't use MBD2 so I re wrote it to use the base DB functons
 * @author Jason Ball
 *
 */
class dbclass
{
	public $error = false;
	protected $db = false;
	protected $auditfields = array();
	protected $table = false;
	protected $auditing = false;
	protected $sequence = false;
	protected $no_empty_string_columns = array(); // columns that should be converted to null if empty string is present
	protected $pkey =false;
	private $dbtimestampformat = "Y-m-d H:i:s";
	protected $valid_fields = array();
	protected $language_id = false;
	
	protected function loadDB()
	{
		if($this->db == false)
		{
			if(isset($GLOBALS['DB']))
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
			
			$this->auditfields['C']['created_by'] = $aperson;
			$this->auditfields['U']['updated_by'] = $aperson;
			$this->auditfields['C']['created_date'] = date($this->dbtimestampformat,time());
			$this->auditfields['U']['updated_date'] = date($this->dbtimestampformat,time());
		}
			
		if($this->table == false)
		{
			print "Invalid Table";
			print $this->cleanBackTrace();
			exit;
		}
			
	}
	
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
	* Pass an array that you want to add or edit and this class will the protected fields varable and 
	* Do a backend validate for you.  Currently I am using the simple R822 for email validatetion,
	* but that is really outdated and something else will be needed.  I just want to try and make this 
	* self containted.
	* examples 
		$val = array('name'=>array('type'=>'varchar',
					'size'=>255,
					'null'=>false),
			 'contact_email'=>array('type'=>'email',
									 'null'=>false),
			  'state'=>array('type'=>'integer',
							 'size'=>2),
			  'country'=>array('type'=>'varchar',
							   'size'=>2),
			  'testdate'=>array('type'=>'date'));

	* @author Jason Ball
	
	*/
	public function validate($fields,$type="edit")
	{
		$this->error = false;
		//Nothing To validate
		if(!is_array($this->valid_fields) || count($this->valid_fields) == 0)
		{
			return true;
		}
		
		//Not passed the right stuff
		if(!is_array($fields))
		{
			$this->error = "Field list must be an array";
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
							$this->error = $k." is a required field, you did not pass it";
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
						$this->error = $k." is a required field, you did not pass it";
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
						if(!is_numeric($fields[$k]))
						{
							$this->error = $k." is a numeric field, you passed invalid data";
							return false;
						}
					}
					
					if($i['type'] == 'date')
					{
						if(!is_numeric($fields[$k]) && trim($fields[$k]) != "")
						{
							if(!is_numeric(strtotime($fields[$k])))
							{
								$this->error = $k." is a date field, you passed invalid data";
								return false;
							}
						}
					}
					if($i['type'] == "email")
					{
						if(trim($fields[$k]) != "" && !$this->isValidEmail($fields[$k]))
						{
							$this->error = $k." is an email field, you passed invalid data in the form of ".$fields[$k];
							return false;
						}
					}
					
				}
				
				if(isset($i['size']))
				{
					if(strlen($fields[$k])>$i['size'])
					{
						$this->error = $k."is of size ".$i['size'].", you passed invalid data";
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
	
	private function isValidEmail($email)
	{
		require_once 'Mail/RFC822.php';
		$mail_rfc = new Mail_RFC822();
		if(!$mail_rfc->isValidInetAddress($email, true))
		{
			return false;
		}
		else
		{
			return true;
		}
		
	} 
	
	public function add($array=array())
	{
		$this->loadDB();
		
		$fields = array();
		$values = array();
		if(is_array($array) && count($array)>0)
		{
		
			if(!$this->validate($array,'add'))
			{
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
			$SQL = "insert into ".$this->table." (".implode(",",$fields).") values (".implode(",",$values).") ";
			if($this->pkey !== false)
			{
				$SQL .= " returning ".$this->pkey;
			}
			
			$result = $this->db->query($SQL);
			
			if($this->pkey !== false)
			{
				$tmp = $result->fetchArray();
				if(isset($tmp[0]))
				{
					return $tmp[0];
				}
			}
			
			return true;
		}
		return false;
		 
	}
	
	public function update($keys=array(), $values = array())
	{
		$this->loadDB();
			
		if(!is_array($keys))
		{
			if(!$this->pkey)
			{
				print "Invalid update keys";
				print $this->cleanBackTrace();
				exit;
				return false;
			}
			$tmp[$this->pkey] = $keys;
			$keys = $tmp;
			
		}
		
		if(!is_array($values))
		{
			print "Invalid update Values";
			print $this->cleanBackTrace();
			exit;
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
			if(trim($i) == "")
			{
				$values[$k] = null;
			}
		
		}
		
		$this->db->update($this->table,$values,$keys);
		
		
		
	}
	
	public function addOrUpdate($keys,$values)
	{
		if(!is_array($keys) || !is_array($keys))
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
			$this->update($keys,$values);
			
			if($this->pkey && isset($test[0][$this->pkey]))
			{
				return $test[0][$this->pkey];
			}
		}
	}
	
	public function get($keys=array(),$options = array())
	{
		
		$ops = defaultArgs($options, array('single'=>false,
											'orderby'=>FALSE,
											'printsql'=>false,
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
				print("Invalid search");
				print $this->cleanBackTrace();
				exit;
			}
			
			$tmp[$this->pkey] = $keys;
		
		}
		
		$junk = $this->getWhere($tmp);
		$SQL = "Select * from ".$this->table;
		if(isPopArray($junk))
		{
			$SQL .= " where ".implode(" and \n ",$junk);
		}
		
		
		if($ops['orderby'])
		{
			$SQL .= " order by ".$ops['orderby'];	
		}
		
		if($ops['printsql'])
		{
			printSQL($SQL);
			print("<BR/>");
		}
		
		$result = $this->db->query($SQL);
		
		if(!is_array($keys))
		{
			$row = $result->fetchRow();
			return $row;
		}
		
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
	
	public function getOne($keys)
	{
		$row = $this->get($keys,array('single'=>true));
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
	
	public function delete($keys)
	{
		$this->loadDB();
		if(!is_array($keys))
		{
			if($this->pkey == false)
			{
				print("Invalid Values");
				print $this->cleanBackTrace();
				exit;
			}
			
			$tmp[$this->pkey] = $keys;
			$keys = $tmp;
		}
		
		
		$junk = $this->getWhere($keys);
		
		if(count($junk)>0)
		{
			$SQL = "delete from ".$this->table." where ".implode(" and \n ",$junk);
			$this->db->query($SQL);
			return true;
		}
		
		return false;
		
	}
	
	private function getWhere($array=array())
	{
		$tmp = array();
		foreach($array as $k => $i)
		{
			if(!is_array($i) && trim(strtolower($i)) == "null")
			{
				$tmp[] = $k." is null";
			}
			if(!is_array($i) && trim(strtolower($i)) == "is not null")
			{
				$tmp[] = $k." is not null";
			}
			else 
			{
				$j = $this->escapeChar($i);
				if($j != false)
				{
					if($j == 'null')
					{
						$tmp[] = $k." is null";
					}
					else 
					{
						if(is_array($j))
						{
							$tmp[] = $k." in ('".implode("','",$j)."')";
						}
						else if(strpos($j,"%")!==false)
						{
							$tmp[] = $k ." like ".$j;
						}
						else 
						{						
							$tmp[] = $k." = ".$j;
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
		if(is_numeric($data))
		{
			return $data;
		}
		elseif(trim($data) == "")
		{
			return 'null';
		}
		else 
		{
			
			return "'".$this->db->escape($data)."'";
		}
		
		return false;
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
