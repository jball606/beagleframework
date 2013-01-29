<?php
include("setincludepath.php");
include("config/systemsetup.php");

if($DB->getDBType() == "mysql")
{
	$SQL = "show tables in ".$dsn['dbname'].";";
}
else
{
	$SQL = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public';";
}

$results = $DB->getAll($SQL);

foreach($results as $row)
{
	if($DB->getDBType() == "pgsql")
	{
		$bob = new tablemaker($row['table_name']);
	}
	else 
	{
		$bob = new tablemaker($row['Tables_in_'.$dsn['dbname']]);
	}
	
	
	
}



class tablemaker// extends beaglebase
{
	private $tablename = false;
	private $pkey = false;
	private $fields = array();
	private $has_not_null = false;
	private $db = false;
	
	public function __construct($table_name)
	{
		global $DB;
		$this->db = $DB;
	
		$this->tablename = $table_name;
		$this->getPrimaryKey();
		if($this->db->getDBType() == "mysql")
		{
			$this->getFields();
		}
		
		$this->writeModel();
	}
	
	private function writeModel()
	{
		$tmp = '<?php '."\n";
		$tmp .= "class ".$this->tablename." extends beagleDbClass\n";
		$tmp .= "{ \n";
		$tmp .= '	protected $table = "'.$this->tablename.'";'."\n";
		if($this->pkey)
		{
			$tmp .= '	protected $pkey = "'.$this->pkey.'";'."\n";
		}
		if($this->has_not_null)
		{
			$tmp .= '	protected $valid_fields = array('."\n";
			foreach($this->fields as $k => $i)
			{
				if(isset($i['null']) && $i['name'] != $this->pkey)
				{
					$tmp .=	"									'".$i['name']."' => array( \n";
					$tmp .= "														'type'=>'".$i['type']."',\n";
					if(isset($i['size']))
					{
						$tmp .= "														'size'=>".$i['size'].",\n";
					}
					$tmp .= "														'null'=>false, \n";
					if(isset($i['preset']))
					{
						$tmp .= "														'preset'=>'".$i['preset']."',\n";
					}
					$tmp .= "													),\n";
				}
			}
														
				
					
			
			
			$tmp .= '							);'."\n";
		}
		
		
		
		$tmp .= "} \n";
		$tmp .= "?>";
		
		file_put_contents(__SYSTEM_ROOT__."/lib/models/".$this->tablename.".php", $tmp);
		print($tmp);
	}
	
	private function getPrimaryKey()
	{
		if($this->db->getDBType() != "pgsql")
		{
			$key = $this->getMySQLPrimaryKey();
		}
		else
		{
			$key = $this->getPGPrimaryKey();
		}
		
		if(isPopArray($key))
		{
			$this->pkey = $key['Column_name'];
		}
		
	}
	
	private function getMySQLPrimaryKey()
	{
		$SQL = "show index from ".$this->tablename." where Key_name = 'PRIMARY';";
		$key = $this->db->query($SQL)->fetchRow();
		return $key;
	}
	
	private function getPGPrimaryKey()
	{
		$SQL = "SELECT  t.table_catalog,
		t.table_schema,
		t.table_name,
		kcu.constraint_name,
		kcu.column_name,
		kcu.ordinal_position
		FROM    INFORMATION_SCHEMA.TABLES t
		LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
		ON tc.table_catalog = t.table_catalog
		AND tc.table_schema = t.table_schema
		AND tc.table_name = t.table_name
		AND tc.constraint_type = 'PRIMARY KEY'
				LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
				ON kcu.table_catalog = tc.table_catalog
				AND kcu.table_schema = tc.table_schema
				AND kcu.table_name = tc.table_name
				AND kcu.constraint_name = tc.constraint_name
				WHERE   t.table_schema NOT IN ('pg_catalog', 'information_schema')
				AND t.table_name = '".$this->tablename."'
				
				ORDER BY t.table_catalog,
				t.table_schema,
				t.table_name,
				kcu.constraint_name,
				kcu.ordinal_position;";
		
		$data = $this->db->query($SQL)->fetchRow();
		if(isset($data['column_name']))
		{
			return array('Column_name'=>$data['column_name']);
		}
		 
		return false;
		
	}
	
	private function getFields()
	{
		$SQL = "show columns from ".$this->tablename.";";
		$cols = $this->db->getAll($SQL);
	
		foreach($cols as $k => $i)
		{
			$tmp = array();
			
			$tmp['name'] = $i['Field'];
			if(!empty($i['Default']))
			{
				$tmp['preset'] = $i['Default'];
			}
			if($i['Null'] == 'NO')
			{
				$tmp['null'] = false;
				$this->has_not_null = true;
			}

			if(strpos(strtolower($i['Type']),"varchar") !== false)
			{
				$tmp['type'] = "varchar";
			}
			elseif(strpos(strtolower($i['Type']),"int") !== false)
			{
				$tmp['type'] = "integer";
			}
			elseif(strpos(strtolower($i['Type']),"date") !== false)
			{
				$tmp['type'] = "date";
			}
			
			if($int = getIntFromString($i['Type']))
			{
				$tmp['size'] = $int;
			}
			
			$this->fields[] = $tmp;
			
		}
	}
}

?>