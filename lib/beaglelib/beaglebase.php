<?php
/**
 * The Beagle Base class is so you don't have to write the same methods over and over again
 * @author Jason Ball
 * @package Beagleframework
 * 
 */
class beaglebase extends beagleerrorbase
{
	
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
	
	/**
	 * Use this method to remove any falses in an arg list
	 * 
	 * @param array $args
	 * @return array $args
	 */
	protected function clearArgs($args =array())
	{
		if(!is_array($args))
		{
			return $args;
		}

		$tmp = array();
		foreach($args as $k => $i)
		{
			if($i !== false)
			{
				$tmp[$k] = $i;
			}
		}
		
		return $tmp;
	}
	
	
	
	/**
	 * Unset the database settings in case you need to serialize the class
	 * 
	 * @param void
	 * @return void
	 */
	public function closeBase()
	{
		if(isset($this->db))
		{
			unset($this->db);
		}
	}
	
	/**
	 * This method returns the 3 letter extention of a file
	 * 
	 * @param string $name
	 * @return string or null
	 * @author Jason Ball
	 */
	protected function getFileType($name)
	{
		$type = substr($name,strlen($name)-4,4);
		if(strpos($type,".")!==false)
		{
			
			return substr($type,1,3);
		}
		return false;	
		
	}
	
	/**
	 * This Method is used to find a specific key in the data array of the child class, 'all' is a special word for giving you the entire array
	 * 
	 * @param array $array
	 * @param string $item
	 * @param boolian $allowall
	 * @return string, array or false
	 * @author Jason Ball
	 */
	protected function getClassData($array, $item,$allowall = true)
	{
		if($this->getError())
		{
			return $this->getError();
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
		elseif($item == "all" && $allowall == true)
		{
			return $array;
		}
		
		return false;
	}
	
	/**
	 * Use this method to load the db object, if you don't pass one, the default is used in GLOBALS[DB]
	 * 
	 * @param resource $db
	 * @return void
	 * @author Jason Ball
	 */
	public function loadSystemDB($db='')
	{ 
		$db = "";
		if(!$this->db && $db == '')
		{
			if (!$db || !is_resource($db))
			{
				if(isset($GLOBALS['DB']))
				{
					$db = $GLOBALS['DB'];
				}
					
			}
			$this->db = $db;
			
			
		}
		else if(!$this->db && $db != '')
		{
			$this->db = $db;
		}
		
	}
	
	/**
	 * This Method is used to render a view page and allow you to pass data or a class to that view page
	 * @param string $filename
	 * @param object/array $result
	 * @author Jason Ball
	 */
	public function showTemplate($filename,$result=false)
	{
		if($this->getError())
		{
			return $this->prettyFail($this->getError());
		}
		
		
		ob_start();
		include($filename);
		return ob_get_clean();
		
	}
	
	/**
	 * This method will return all public and proteced properties
	 * @return Array
	 * @author Jason Ball
	 */
	public function getClassProperties($with_objects=true)
	{
		$tmp = array();
		foreach($this as $k => $i)
		{
			if(is_object($i) && $with_objects == true)
			{
				$tmp[$k] = clone($i);
			}
			else if(!is_object($i))
			{
				$tmp[$k] = $i;
			}
		}
		
		return $tmp;
	}
	
	/**
	 * This method will load all public and proteced properties
	 * 
	 * @param Array $in_args
	 * @return void;
	 * @author Jason Ball
	 */
	public function loadClassProperties($in_args=array())
	{
		
		foreach($in_args as $k => $i)
		{
			if(isset($this->$k) && $k != 'db')
			{
				$this->$k = $i;
			}	
			
		}
	}
	
	
}
?>