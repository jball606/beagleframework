<?php
class beaglebase
{
	protected $error = false;
	protected $db = false;

	
	public function __construct($db='')
	{ 
		$this->loadSystemDB($db);
	}
	
	public function __sleep()
	{
		$this->closeBase();
		return array_keys( (array) $this);
	}
	
	public function __wakeup()
	{
		$this->loadSystemDB();
	}
	
	protected function clearArgs($args =array())
	{
		if(!is_array($args))
		{
			return $args;
		}

		$tmp = array();
		foreach($args as $k => $i)
		{
			if($i)
			{
				$tmp[$k] = $i;
			}
		}
		
		return $tmp;
	}
	
	public function closeBase()
	{
		if(isset($this->db))
		{
			unset($this->db);
		}
	}
	
	protected function getFileType($name)
	{
		$type = substr($name,strlen($name)-4,4);
		if(strpos($type,".")!==false)
		{
			return $type;
		}
		return "";	
		
	}
	
	protected function getClassData($array, $item)
	{
		if($this->error)
		{
			return $this->error;
		}
		
		if(is_array($item))
		{
			$tmp = array();
			foreach($item as $k => $i)
			{
				if(isset($array[$i]) || array_key_exists($i,$array))
				{
					if(is_numeric($k))
					{
						$tmp[$i] = $array[$i];
					}
					else 
					{
						$tmp[$k] = $array[$i];
					}
					
				}
				
			}
			return $tmp;
			
		}
		elseif(isset($array[$item]))
		{
			return $array[$item];
		}
		elseif($item == "all")
		{
			return $array;
		}
		
		return false;
	}
	
	public function loadSystemDB($db='')
	{ 
	
		if(!$this->db && $db == '')
		{
			if (!$db)
			{
				
				$db = $GLOBALS['DB'];
					
			}
	
			$this->db = $db;
			
			
		}
		
	}
	
	
	/**
	 * make the pretty UL fail for good user UI
	 * @param $value (string, usually $this->error)
	 * @return html string or false
	 */
	protected function prettyFail($value="")
	{
		if($value == "")
		{
			return false;
		}
		return '<ul class="erroralert"><li>'.$value.'</li></ul>';
		
	}
	
	public function showTemplate($filename,$result=false)
	{
		if($this->error)
		{
			
			return $this->prettyFail($this->error);
		}
		
		
		ob_start();
		include($filename);
		return ob_get_clean();
		
	}
	
	
	
	
	
}
?>