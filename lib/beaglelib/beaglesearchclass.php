<?php
/**
 * This Class is used to create the search query for a specific controller
 * @author Jason Ball
 *
 */
class beagleSearchClass extends beaglebase
{
	protected $lists;
	/**
	 * This is the array we store the where clasues array[table][field] = want
	 * @var array
	 */
	protected $whereitems = array();
	/**
	 * This is the array we store view items array[table.field] = table.field or sql clause in select statement
	 * @var array
	 */
	protected $viewitems = array();
	
	protected $order = array();
	protected $location = array();
	protected $subwhere = array();
	protected $letterval = false;  
	protected $page = false;
	 
	/**
	 * Method to get the right DB resorce
	 */
	protected function loadSearch()
	{
		$this->loadSystemDB();
	}
	
	/**
	 * Method to clean up the order array and view array so you don't try to order something that doesn't exist
	 * @param string $order
	 * @param array $view
	 * @return string
	 */
	protected  function badOrder($order,$view)
	{
		$tmp = explode(",",$order);
		$final = array();
		foreach($tmp as $i)
		{
			$test = explode(" ",$i);
			foreach($view as $k => $v)
			{
				if($test[0] == $k)
				{
					$final[] = $i;
				}
			}
			
		}
		return join(", ",$final);
	}

	/**
	 * This Method will set the where clause of the SQL
	 * @param array $in_args
	 * @return void
	 * @author Jason Ball
	 */
	public function setWhere($in_args)
	{
		if(isPopArray($in_args))
		{
			$this->whereitems = $in_args;
		}	
		
	}
	
	/**
	 * 
	 * make the array needed to pass a open link
	 * @param array $array (array we are working)
	 * @param array we want to keep $keepkey
	 * @param array value we want to have $valuekey
	 * @return full array
	 */
	protected function makeLinkArray($array,$keepkey,$valuekey)
	{
		if(!is_array($keepkey))
		{
			$tmp[0] = $keepkey;
			$keepkey = array();
			$keepkey[] = $tmp[0];
		}

		if(!is_array($valuekey))
		{
			$tmp[0] = $valuekey;
			$valuekey = array();
			$valuekey[] = $tmp[0];
		}
		
		$save = array();
		foreach($keepkey as $keep)
		{
			if(isset($array[$keep]))
			{
				$save[$keep]['value'] = $array[$keep];
				
				foreach($valuekey as $value)
				{
					if(isset($array[$value]))
					{
						$save[$keep]['params'][] = $array[$value];	
					}
					
				}
			}
				
		}
			
		
		$done = array();
		foreach($array as $k => $i)
		{
			if(isset($save[$k]))
			{
				$done[$k] = $save[$k];
			}
			else 
			{
				$done[$k] = $i;
			}
			
		}
		foreach($valuekey as $i)
		{
			if(isset($done[$i]))
			{
				unset($done[$i]);
			}
		}
		return $done;
	}
	
	/**
	 * This method is used to clean up the view data so it works in the SQL statement
	 * @param array $array
	 * @return array $array
	 * @author Jason Ball
	 */
	protected function stringClean($array)
	{
		$f[] = 'date_s__';
		$f[] = 'date_e__';
		$r[] = '';
		$r[] = '';
		
		if(!is_array($array))
		{
			return str_replace($f,$r,$array);
		}
		else
		{
			$tmp = array();
			foreach($array as $k => $i)
			{
				$tmp[$k] = str_replace($f,$r,$i);
				if($tmp[$k] == "")
				{
					$tmp[$k] = $k;
				}
			}
			
			return $tmp;
		}
		
	}
	
	/** 
	 * You need to make this method in your class if you want to use the letter system or other 
	 * @param string $where
	 */
	public function loadSubWhere($where)
	{
		
	}
	
	/**
	 * Actual method that creates the search array and gets you all the data
	 * @param array $in_args
	 * @example in_args(
	 * first =>			0,			Begining of the search page
	 * limit =>			20,			Number of results on the page
	 * excel =>			FALSE,		Do you want this as an excel item.  Actually just returns the full SQL statement to be used by the excel clas
	 * SQL_F =>			false,		The from section of yoru SQL statement
	 * extrawhere =>	array(),	array of extra where clasues that may not fix the standard array system
	 * key =>			array()		primary key of search exapmle :('id'=>false,'name'=>false,'sqlkey'=>false)
	 * all =>			false,		return all records, skips first and limit
	 * printsql =>		false,		for debugging, this will show you the exact SQL statement
	 */
	protected function executSearch($in_args = array())
	{
		
		$args = defaultArgs($in_args,array('first'=>0,
											'limit'=>20,
											'excel'=>FALSE,
											'SQL_F' => false,
											'extrawhere'=>array(),
											'key'=>array('id'=>false,'name'=>false,'sqlkey'=>false),
											'all'=>false,
											'printsql'=>false,
											));
											
											
		if($args['SQL_F'] == false)
		{
			return false;
		}
		
		$view = $this->stringClean($this->viewitems);
		
		if($args['excel'] == false)
		{
			if(is_array($args['key']) && $args['key']['id'] != false && $args['key']['name'] != false)
			{
				$view[$args['key']['id']] = $args['key']['name'];
			}
		}
		
		
		$SQL_S = " select ".implode($view,",")." ";
		
		$SQL_F = $args['SQL_F'];
		
		
		$where = $this->whereitems;
		
		foreach($args['extrawhere'] as $k => $i)
		{
			$where[$k] = $i;
		}
		
		$wh = $this->getWhere($where);
		
		if($args['all'] == false)
		{
			$wh = $this->mergeSubWhere($wh);
		}
		
		$SQL_W = "";
		
		if($wh != false)
		{
			$SQL_W = " WHERE ".$wh;
			
		}
		
		
		$SQL_G = " group by ".implode(",",$this->getGroupData($view));
		
		$SQL = $SQL_S." \n ".$SQL_F." \n ".$SQL_W." \n ".$SQL_G;
		
		if($o = $this->createOrder())
		{
			$SQL .= " order by ".implode($o,",");
		}
		
		if($args['excel'] == true)
		{
			return $SQL;
		}
	
		//May be the most correct but slower $PRE_SQL = "select sum(c) from (select count(*) as c ".$SQL_F." ".$SQL_W." ".$SQL_G.") as foo";
		$PRE_SQL = "select count(*) as c ".$SQL_F." ".$SQL_W;
		if($args['printsql'])
		{
			print("<br/>PRE-SQL <BR>");
			printSQL($PRE_SQL);
		}
		$R = $this->db->getOne($PRE_SQL);
		$result = array();
		
		$result['total_records'] = $R;

		
		if(is_numeric($args['limit']))
		{
			$SQL .= $this->db->limitOffset($args['limit'],$args['first']);
		}

		$this->location['first'] = $args['first'];
		$this->location['limit'] = $args['limit'];
		
		if($args['printsql'])
		{
			print("<br/>POST SQL<BR>");
			printSQL($SQL);
		}
		
		$R = $this->db->query($SQL);
		$tmp = array();
		while($row = $R->fetchRow())
		{
			if($args['key']['sqlkey'] != false)
			{
				$tmp[$row[$args['key']['sqlkey']]] = $row;
			}
			else 
			{
				$tmp[] = $row;
			}	
		}
		
		$result['records'] = $tmp;
		
		return $result;
	}
	
	/**
	 * Method to create order clause of search statemnt
	 */
	private function createOrder()
	{
		$tmp = array();
		foreach($this->order as $k => $i)
		{
			if($i == 1)
			{
				$tmp[] = $k." ASC";
			}
			if($i == 2)
			{
				$tmp[] = $k." DESC";
			}
		}
		if(count($tmp)>0)
		{
			return $tmp;
		}
		return false;
	}
	
	private function mergeSubWhere($wh)
	{
		if(count($this->subwhere)>0)
		{
			if($wh != false)
			{
				$wh .= " and ".implode(" and \n ",$this->subwhere);
			}
			else 
			{
				$wh .=  implode(" and \n ",$this->subwhere);
			}
			
		}	
		
		return $wh;
	}
	
	/**
	 * Get the group by part of the search SQL string
	 * @param array $array	Passing the view data to get the group by clause
	 */
	private function getGroupData($array=array())
	{
		$tmp = array();
		if(is_array($array))
		{	
			foreach($array as $k => $i)
			{
				$tmp[] = $k;
			}
		
			if(isPopArray($this->order))
			{
				foreach($this->order as $k => $i)
				{
					if(!isset($array[$k]))
					{
						$tmp[] = $k;
					}
				}
			}
						
					
			return $tmp;
		}
	}
	
	public function loadWhereItems($in_args=array())
	{
		$this->whereitems = $in_args;	
	}
	
	public function loadViewItems($in_args= array())
	{
		foreach($in_args as $k => $i)
		{
			$this->viewitems[$k] = $i;
		}
	}
	
	public function getWhereData($item)
	{
		return $this->getClassData($this->whereitems, $item);
	}
	
	
	protected function setAnd($and,$info)
	{
		foreach($and as $k => $i)
		{
			if(is_array($i))
			{
				
				$key =array_keys($i);
				if($i[$key[0]] == 'and')
				{
					if(isset($info['where'][$k][$key[0]]))
					{
						$tmp = $info['where'][$k][$key[0]];
						unset($info['where'][$k][$key[0]]);
						$info['where'][$k][$key[0]]['and'] = $tmp;
					}
				}
			}
			else
			{
				if($i == 'and')
				{
					if(isset($info[$k]))
					{
						$tmp = $info[$k];
						unset($info[$k]);
						$info[$k]['and']= $tmp;
					}
				}
			}
		}
		return $info;
	}

	protected function saveView($info,$type)
	{
		$_SESSION[$type.'_saveview'] = $info;	
		
	}
	
	/**
	 * Takes the where array and converts it into an array of properly executledable where staements
	 * @param array $info
	 * @return array
	 * @example Clauses
	 * !null = 	is not null and != ''
	 * isnull = is null
	 * null =	not null
	 * !(item)	!= item 
	 */
	private function getWhere($info)
	{
		$clause = "";
		foreach($info as $table => $i)
		{
			if($table == "passthru")
			{
				$clause .= $i." and ";
			}
			else if($table != "and")
			{
				foreach($i as $field => $v)
				{
					if(is_array($v) || trim($v) != "")
					{
						if(is_array($v))
						{
							$loc = "or";
							$tclause = " (";
							foreach($v as $key => $vi)
							{
								if(is_array($vi))
								{
									$loc = $key;
									//Add or logic where[table][field][and/or][]	
									foreach($vi as $data)
									{
										if($data != "")
										{
											$tclause .= $table.".".$field." = ".$data." ".$loc." "; 	
										}
									}
								}
								else
								{
									if(trim($vi) != "")
									{
										$tclause .= $table.".".$field." = '".$vi."' or ";
									}
								}
							}
							if(strlen($tclause)>strlen($loc))
							{
								$clause .= substr($tclause,0,strlen($tclause)-(strlen($loc)+2));
								$clause .= ") and ";
							}
						}
						
						elseif(strpos($v,"!null") !== false)
						{
							$clause .= "(".$table.".".$field." != '' and ".$table.".".$field." is not null) and ";
						} 
						elseif(strpos($v,"!")!==false)
						{
							$clause .= "(".$table.".".$field." ".$v." or ".$table.".".$field." is null) and ";
							
						}
						elseif(strpos($v,"null")!== false)
						{
							if($v == "isnull")
							{
								$clause .= $table.".".$field." is null and ";
							}
							else
							{
								$clause .= $table.".".$field." is not null and ";
							}
						}
						elseif(strpos($field,"__")!==false)
						{
							$tmp = preg_split("/__/",$field);
							$v = str_replace("%",'',$v);
							if($tmp[0] == "date_s")
							{
								$d = strtotime($v);
								$clause .= $table.".".$tmp[1]." >= '".date("Y-m-d",$d)."' and ";
							}
							if($tmp[0] == "date_e")
							{
								$d = strtotime($v)+86000;
								$clause .= $table.".".$tmp[1]." <= '".date("Y-m-d",$d)."' and ";
							}
	
						}
						else
						{
							
							$clause .= $this->db->getDbWhere($table,$field,$v);
								
							
						}
						
					}
				}
			}
		}
		if(strlen($clause)>0)
		{
			return substr($clause,0,strlen($clause)-5);
		}
		else
		{
			return false;
		}
	}

	protected function getRList($table,$key)
	{
		$tmp = array();
		$SQL = "select $key from $table group by $key order by $key";
		
		$result = $this->db->query($SQL);
		while($row = $result->fetchRow())
		{
			$tmp[$row[$key]] = 0;
		}
		return $tmp;
	}

	

	
}
?>