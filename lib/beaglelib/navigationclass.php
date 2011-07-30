<?php
class navigationclass extends beaglebase
{
	
	protected $check = array();
	protected $allcheck = false;
	protected $checkdir = true; //true means only checked, fall means checked as only not checked
	
	protected function createLetterMenu($SQL,$key)
	{
		$tmp = array();
		if($key != "")
		{
			$SQL = "select lower(substr(".$key.",1,1)) as letter,
					count(*) as cnt
					from ( ".$SQL." ) as foo
					group by  lower(substr(".$key.",1,1))
					order by  lower(substr(".$key.",1,1));";
			
			
			$result = $this->db->query($SQL);
			while($row = $result->fetchRow())
			{
				$tmp[$row['letter']] = $row['cnt'];
			}
		}
		else 
		{
			$SQL = "SELECT count(*) as cnt from (".$SQL.") as tab";
			$count = $this->db->getOne($SQL);

		
		}

		$res = $this->createLetterResults($tmp);

		if (!$key)
		{
			$res['_'] = $count;
		}
		
		
		return $res;
	}
	
	private function createLetterResults ($have)
	{
		$tmp = array('_' => 0);

		foreach (range('a', 'z') as $letter)
		{
			$tmp[$letter] = 0;

			if (isset($have[$letter]))
			{
				$tmp[$letter] = $have[$letter];
				unset($have[$letter]);
			}
		}

		foreach ($have as $letter => $count)
		{
			$tmp['_'] += $count;
		}

		return $tmp;
	}
	
	public function getSelected()
	{
		if($this->allcheck == true)
		{
			return 'all';
		}
		if(count($this->check)>0)
		{
			$tmp = array('checkdir'=>$this->checkdir,
							'selected'=>$this->check);
			
			return $tmp;
		}

		return false;
	}
	
	private function letterCheck($key)
	{
		return "upper(substring(".$this->db->escape($key).", 1, 1))";
	}
	
	public function loadCheck($value,$ck)
	{
		if($this->checkdir == true)
		{
			if($ck == 'true')
			{
				$this->check[$value] = $value;
			}
			else
			{
				if(isset($this->check[$value]))
				{
					unset($this->check[$value]);
				}
				
			}
		}
		else if($this->checkdir == false) //All But
		{
			$this->allcheck = false;
			if($ck == 'false')
			{
				$this->check[$value] = $value;
			}
			else
			{
				if(isset($this->check[$value]))
				{
					unset($this->check[$value]);
				}
				
			}
		}
	}
	
	protected function loadLetterNav($array)
	{
		if(is_array($array))
		{
			$k = array_keys($array);
			$key1 = $k[0];
			
			if(is_array($array[$key1]))
			{
				$k2 = array_keys($array[$key1]);
				$key2 = $k2[0];

				$value = $array[$key1][$key2];
				if($value == "_")
				{
					$this->subwhere['letter'] = $this->letterCheck($key1.".".$key2)." !~'[A-Z]'";
					$this->letterval = $value;	
				}
				elseif($value == "all")
				{
					unset($this->subwhere['letter']);
					$this->letterval = false;
				}
				elseif($value != "") 
				{
					$this->subwhere['letter'] = "upper(".$this->letterCheck($key1.".".$key2).") like upper('".$value."%') ";
					$this->letterval = $value;	
				}
				
			}
			
		}
		
		return false;
	}
	
	public function setAllCheck($check)
	{
		if($check == 'true')
		{
			$this->allcheck = true;
			$this->checkdir = false;
			$this->check = array();
		}
		elseif($check == 'false')
		{
			$this->allcheck = false;
			$this->checkdir = true;
		}
	}
	
	public function resetCheck()
	{
		$this->allcheck = false;
		$this->checkdir = true;
		$this->check = array();
	}
	
}