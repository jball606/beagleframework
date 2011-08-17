<?php
abstract class searchclass extends navigationclass
{
	protected $lists;
	protected $whereitems = array();
	protected $viewitems = array();
	protected $order = array();
	protected $location = array();
	protected $subwhere = array();
	protected $letterval = false;  
	protected $page = false;
	
	protected function loadSearch()
	{
		$this->loadSystemDB();
	}
	
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
	 * This method will return a clean name of a DB filed name
	 * @param string $name
	 * @return string $name
	 * @author Jason Ball
	 * @copyright 2011-08-02
	 */
	protected function standardTitle($name)
	{
		if(strpos($name,'.')!==false)
		{
			$name = substr($name,strpos($name,'.')+1,strlen($name));
		}
		
		return ucwords(str_replace("_"," ",$name));
	}
	
	abstract protected function cleanName($name);
	

	
	protected function globalCleanName($name)
	{
		if($name == "users.email")
		{
			return "Email";
		}
		if($name == "company.company_name")
		{
			return "Company Name";
		}
		
		if($name == "account_contact.first_name, account_contact.last_name")
		{
			return "Account Team Contact";
		}
		
		return $name;	
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
			}
			return $tmp;
		}
		
	}
	
	abstract protected function run_Search();
	
	abstract public function showResultsPage();
	
	/** 
	 * You need to make this method in your class if you want to use the letter system or other 
	 * @param string $where
	 */
	public function loadSubWhere($where)
	{
		
	}
	
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
	
		if($args['printsql'])
		{
			print("<br/>PRE-SQL <BR>");
			printSQL($SQL);
		}
		$R = $this->db->query($SQL);
		$result = array();
		
		$result['total_records'] = $R->numRows();

		
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
	
	
	protected function createOrder()
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
	
	protected function mergeSubWhere($wh)
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
	
	
	
	protected function runResultPage($in_args = array())
	{
		
		$args = defaultArgs($in_args,array('first'=>false, //Start of result list
											'limit'=>false, //How many per page
											'orderby'=>false, //Order field
											'orderdir'=>false, //Order Dir 1 (ASC) 2 (DESC)
											'page'=>'',
											'title'=>false, //Title on header bar
											'dates'=>array(),	//Date fields that need formatting example 'dates'=>array('created_date'=>"m/d/Y"),
											'link'=>array(), 	//Link Array' exmpale array(0 => array('field'=>'email','key'=>'upid')),
											'edit_pencil'=>array(),
											'bottommenu'=>false, //If you want links at the bottom
											'lib'=>'search',	//Javascript class name
											'sel'=>false,	//Do you need select bcxes for the row? false or array(name,key) of box
											'lettermenu'=>array(), //name = title and key = dbid
											'showemptyresult'=>false,
											'showperpage'=>true,
											'showcount'=>true,
											'extra'=>array(),
											'editaccess'=>false, //Used to give you the popup to select who can and can not see your information
											'allowsort'=>true, //Allow the user to sort a row
											'hiddenrows'=>array(),	
										));
											
											
											
		if($args['orderby'])
		{
			if(isPopArray($args['orderby']) && isPopArray($args['orderdir']))
			{
				foreach($args['orderby'] as $k => $i)
				{
					$this->order[$i] = $args['orderdir'][$k];
				}
			}
			
			if(is_numeric($args['orderdir']) && $args['orderdir'] !== false)
			{
				if($args['orderdir'] == 1 || $args['orderdir'] == 2)
				{
					$this->order[$args['orderby']] = $args['orderdir'];
				}
				else 
				{
					if(isset($this->order[$args['orderby']]))
					{
						unset($this->order[$args['orderby']]);
					}
				}
			}
		}
		
		if(isPopArray($args['hiddenrows']))
		{
			$tmp = array();
			foreach($args['hiddenrows'] as $k => $i)
			{
				if(is_numeric($k))
				{
					$tmp[$i] = $i;
				}
				else 
				{
					$tmp[$k] = $i;
				}
				
			}
			$args['hiddenrows'] = $tmp;
		}
		
		if($args['first'] === false && isset($this->location['first']))
		{
			$args['first'] = $this->location['first'];
		}
		elseif(!$args['first']) 
		{
			$args['first'] = 0;
		}
	
		if(!$args['limit'] && isset($this->location['limit']))
		{
			$args['limit'] = $this->location['limit'];
		}
		elseif(!$args['limit']) 
		{
			$args['limit'] = 10;
		}
		
		$result = $this->run_Search($args['first'],$args['limit']);

		$result['title'] = $args['title'];
		$result['headers'] = $this->getHeaders($this->viewitems);
		$result['limit'] = $args['limit'];
		$result['editaccess'] = $args['editaccess'];
		$result['allowsort'] = $args['allowsort'];
		$result['hiddenrows'] = $args['hiddenrows'];
		
		$result['first'] = $args['first'];
		$result['dates'] = $args['dates'];
		if(isPopArray($args['edit_pencil']))
		{
			$result['edit_pencil'] = true;
			
		}
		$result['order'] = $this->order;
		$result['lib'] = $args['lib'];
		$result['showperpage'] = $args['showperpage'];
		$result['showcount'] = $args['showcount'];
		$result['showemptyresult'] = $args['showemptyresult'];
			
		if(isset($args['lettermenu']['name']))
		{
			$result['lettermenu']['name'] = $args['lettermenu']['name'];
			$result['lettermenu']['list'] = $this->createLetterMenu($this->run_search($args['first'],$args['limit'],true,true),$args['lettermenu']['key']);
			$result['lettermenu']['sel'] = $this->letterval;
		}
		
		if($args['sel'] !== false)
		{
			$result['sel'] = $args['sel'];
			$result['check'] = $this->check;
		}
		$result['bottommenu'] = $args['bottommenu'];
		
		$result['orgdata'] = $result['records'];
		if(isPopArray($args['extra']))
		{
			foreach($args['extra'] as $k => $i)
			{
				$result[$k] = $i;
			}
		}
		
		$junk = array();
		
		if((isPopArray($args['link'])) || (isset($args['edit_pencil']) && isPopArray($args['edit_pencil'])))
		{
			foreach($result['orgdata'] as $k => $i)
			{
				if(isset($args['edit_pencil']) && isPopArray($args['edit_pencil']))
				{
					$i['edit_pencil'] = '';
					$args['link']['field'] = 'edit_pencil';
					$args['link']['key'] = $args['edit_pencil']['key'];
					
				}
				
				$junk[$k] = $this->makeLinkArray($i, $args['link']['field'], $args['link']['key']);
				
			}
			$result['records'] = $junk;
		}
		
		
		if($this->page == false)
		{
			if($args['page'] == "" || $args['page'] == false)
			{
				$args['page'] = getView("resultlist.php",'beagleviews');
			}
			else 
			{
				$this->page = $args['page'];
			}
		}
	
		return $this->showTemplate($args['page'],$result);
		
	}
	
	protected function getGroupData($array=array())
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
	
	protected function getHeaders($array=array())
	{
		if(is_array($array))
		{
			$tmp = array();

			foreach($array as $k => $i)
			{
				$tmp[$k] = $this->cleanName($k);	
			}
		}
		
		return $tmp;
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
	
	protected function getWhere($info)
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

	protected function excel($SQL,$views)
	{
		$tmp = array();
		foreach($views as $k => $i)
		{
			$tmp[0][] = $this->cleanName($k,$i);
		}
		$result = $this->db->query($SQL);
		while($row = $result->fetchRow())
		{
			foreach($row as $k => $i)
			{
				if(strpos(strtolower($k),"date")!==false && $i != '' && is_numeric($i))
				{
					$row[$k] = date("m/d/Y",$i);
				}
				if(strpos(strtolower($k),"deadline") !== false && $i != '')
				{
					$row[$k] = date("m/d/Y",$i);
				}
			}
			$tmp[] = $row;	
		}
		return $tmp;
	}

	
}
?>