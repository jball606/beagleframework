<? 
class mondb
{
	private $dbconn = false;
	private $error = false;
	private $conn = array();
	
	const dbtype = "mongodb";
	
	public function __construct($in_args = array())
	{
		$args = $this->defaultArgs($in_args, array('host'=>'localhost',
													'port'=>'27017',
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
		//	unset($this->dbconn);
		}
		if(isset($this->db))
		{
			unset($this->db);
		}
		return array_keys(get_object_vars($this));
	}

	public function __wakeup()
	{
		$this->loadDB();
	}
	
	private function loadDB()
	{
		if($this->conn['host'] != false)
		{
			$string = "mongodb://";
			if($this->conn['user'])
			{
				$string .= $this->conn['user'];
			}
			if($this->conn['password'])
			{
				$string .= ":".$this->conn['password']."@";
				
			}
			$string .= $this->conn['host'];
			
			$this->dbconn = new MongoClient($string);
			/*
			if($check == false)
			{
				print("Can't find database");
				print("<BR>".mysql_error());
				exit;
			}
			*/
		}
	}
	
	public function add($database, $collection, $array)
	{
		if(isPopArray($array))
		{
			$set = $this->dbconn->selectCollection($database,$collection);
			$result = $set->insert($array);
			
			if(isset($array['_id']))
			{
				return $array['_id']->{'$id'};
			}
		}
		
		return false;
	}

	public function update($database, $collection, $criteria, $values)
	{
		$set = $this->dbconn->selectCollection($database,$collection);	
		$set->update($criteria,$values,array('multiple'=>true));
		
		return true;
		
	}
	
	public function updateById($database, $collection, $id, $values)
	{
		$set = $this->dbconn->selectCollection($database,$collection);	
		$set->update(array('_id'=> new MongoId($id)),$values);
		return true;
	}
	
	public function query($database, $collection, $array)
	{
		$set = $this->dbconn->selectCollection($database,$collection);	
		return $set->find($array);
	}
	
	public function queryById($database,$collection,$id)
	{
		$set = $this->dbconn->selectCollection($database,$collection);	
		return $set->findOne(array('_id'=> new MongoId($id)));
		
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
}
?>