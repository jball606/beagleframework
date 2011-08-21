<?
/**
 * Tools used for list pulldowns
 * @author Jason Ball
 * 11/25/2010
 */
class beagleListTools extends beaglebase
{
		
	public function loadListDB($db="")
	{
		$this->loadSystemDB($db);
	
	}

	public function __sleep()
	{
		if(isset($this->db))
		{
			unset($this->db);
		}
		if(isset($this->dbclass))
		{
			unset($this->dbclass);
		}
		if(isset($this->dbsys))
		{
			unset($this->dbsys);
		}
		
		return array_keys(get_object_vars($this));	
	}
	
	public function __wakeup()
	{
		$this->loadListDB();	
	}

	protected function Selector($have,$want,$type="")
	{
		if($have == "")
		{ 
			return false;
		}
		else
		{
			if(!is_array($have) && strpos($have,",") == 0)
			{ 
				if($have == $want)
				{ 
					if($type == "" || $type == "select")
					{
						return 'selected = "selected"';
					}
					elseif($type == "raw")
					{
						return true;
					}
					else
					{
						return "checked";
					}
				}
			}
			else
			{
				if(is_array($have))
				{
					$a = $have;
				}
				else
				{
					$a = explode(",",$have);
				}

				for($b=0;$b<count($a);$b++)
				{ 
					if($a[$b] == $want)
					{ 
						if($type == "" || $type == "select")
						{
							return 'selected = "selected"';
						}
						elseif($type == "raw")
						{
							return true;	
						}
						else
						{
							return "checked";
						}
					} 
				}
			} 
		}

		return false; 
	}

	protected function Counter($start=0,$end=31,$old,$default="")
	{
		$xml = $default;
		for ($i = $start; $i <= $end; $i++)
		{ 
			$xml .= '<option value = "'.$i.'" '.$this->Selector($i,$old).">$i</option>\n"; 
		}
		return $xml;	
	}

	protected function OldCheck($old)
	{
		if($old == "" || $old == "null")
		{
			 return false; 
		}
		else
		{
			 return true; 
		}
	}
	
	/**
	 * This function returns XML of field values for contacts matching the provided parameters.
	 *
	 * @param $key the column to be used as the unique value
	 * @param $field the name column to be used in the display part of the list item
	 * @param $SQL the sql query to run to produce the list
	 * @param $old
	 * @param $default a default value to return
	 * @param $output P returns html option tags around values, S returns just the selected value (plain text), B both, A for Array
	 * @param $list list_id of values to fetch
	 * @param $single P{ulldown} returns html option tags around values, S{ingle} returns just the selected value (plain text), B{oth} return both items
	 * @param $type the type of list to be created {radio,select,checkbox}
	 * @param $name If $type is present, the name of the html field
	 * @param $multiselect if true make a multiselect list
	 * @param $size The size of the pick list for multiselect fields
	 * @access public
	 * @return mixed variable
	 * @author Jason Ball
	 */
	protected function SelectedGen($key, $field, $SQL, $old="", $default="", $output="P", $type="", $name="", $multiple=0, $size=0)
	{
		$result = $this->db->getAll($SQL);
		if(trim($old) == "null")
		{
			$old = "";
		}
		return $this->_SelectedGen($key, $field, $result, $old, $default, $output);
	}

	/**
	 * see SelectedGen docs
	 */
	protected function SelectedGenArray($key, $field, $result, $old="", $default="", $output="P", $type="", $name="", $multiple=0, $size=0)
	{
		# you better skip any header records yourself or add a flag to this function
		return $this->_SelectedGen($key, $field, $result, $old, $default, $output, $type, $name, $multiple, $size);
	}

	/**
	 * see SelectedGen docs
	 */
	private function _SelectedGen($key, $field, $result, $old="", $default="", $output="P")
	{	
		//P = Pulldown
		//S = Single
		//B = Both
		//A = Array
		$single = "";
		$xml = "";
		$value = "";
				
		if($output == "A")
		{
			$tmp = array();
			if(trim($default) != "")
			{
				$tmp[0]['id'] = "";
				$tmp[0]['value'] = $default;
			}
			
			foreach($result as $row)
			{
				$x = count($tmp);
				$tmp[$x]['id'] = $row[$key];
				$tmp[$x]['value'] = $row[$field];
				if($this->Selector($old, $row[$key],'raw'))
				{
					$tmp[$x]['selected'] = 1;
				}
				
			}
			
			return $tmp;
		}
		if(is_array($default))
		{
			$xml .= '<option value = "'.$default['value'].'">'.$default['label']."</option>\n";	
		}
		elseif (trim($default) != "")
		{
			$xml .= '<option value = "">'.$default."</option>";
		}
		
					
		if(is_array($old))
		{
			$oldlist = $old;
		}
		else
		{
			$oldlist = explode(",",$old);
		}
		
		if($old != "")
		{
			foreach ($result as $row)
			{ 
				foreach ($oldlist as $okey => $oval)
				{
					if($oval == $row[$key])
					{ 
						if(strlen($single)>0)
						{
							$single .= ", ".$row[$field];
							$value .= ", ".$row[$key];
						}
						else
						{
							$single = $row[$field];
							$value = $row[$key];
						}
					}
				}
							
				if($output == "P")
				{
					$xml .= '<option value = "'.$row[$key].'" '.$this->Selector($old,$row[$key]).'>'.htmlentities($row[$field])."</option>\n";
				}
				
			}
		}
		else
		{
			if($output == "S")
			{
				return "";
			}

			foreach ($result as $row)
			{ 
				if($output == "P")
				{
					$xml .= '<option value = "'.$row[$key].'">'.htmlspecialchars($row[$field])."</option>\n";
				}
				
			}
		}
		
		if($output == "P")
		{
			return $xml;
		}
		elseif($output == "S")
		{
			return $single;
		}
		else
		{
			$info[0] = $single;
			$info[1] = $xml;
			$info[2] = $value;
			return $info;
		}
	}

	public function reverseList($in_args = array())
	{
		$args = defaultArgs($in_args,array('table'=>false,
											'field_key'=>false,
											'field' => false,
											'value'=>false));

		if($args['table'] == false || $args['field'] == false || $args['value'] === false || $args['field_key'] == false)
		{
			return false;
		}

		if(trim($args['value']) == "")
		{ 
			return false;
		}
		
		$SQL = "Select ".$args['field']." from ".$args['table']." where ".$args['field_key']." = '".$args['value']."';";

		return $this->db->getOne($SQL);
		
	}
	
	protected function Genericinfo($table,$key,$field,$SSQL,$default,$old,$xslt,$single)
	{
		$xml = "";
		if($old != "" and $old != "null")
		{
			$SQL = "Select * from ".$table." where ".$key." = '$old';";
			$row = $this->db->fetch_array($this->db->query($SQL,"em"));
			$xml .= '<option value = "'.$row[$key].'">'.hs($row[$field]).'</option>'."\n";
						
		}
		if($old == "" and $single=="N")
		{
			if($old == "" and $default != "")
			{
				 $xml .= $default; 
			}
						
			$result = $this->db->query($SSQL,"em");
			while($row = $this->db->fetch_array($result))
			{
				 $xml .= '<option value = "'.$row[$key].'">'.hs($row[$field]).'</option>'."\n"; 
			}
		}
		if($xslt == "Y")
		{
			 return $xml; 
		}
		else
		{ 
			print $xml;
			return false;
		}
				
	}
	
}	
?>