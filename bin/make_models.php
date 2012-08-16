<?php
include("setincludepath.php");
include("config/systemsetup.php");

$SQL = "show tables in ".$dsn['dbname'].";";

$results = $DB->getAll($SQL);

foreach($results as $row)
{

	$bob = new tablemaker($row['Tables_in_'.$dsn['dbname']]);
	
	
	
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
		$this->getFields();
		
		$this->writeModel();
	}
	
	private function writeModel()
	{
		$tmp = '<?php '."\n";
		$tmp .= "class ".$this->tablename." \n";
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
				if(isset($i['null']))
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
					$tmp .= "													);\n";
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
		$SQL = "show index from ".$this->tablename." where Key_name = 'PRIMARY';";
		$key = $this->db->query($SQL)->fetchRow();
		if(isPopArray($key))
		{
			$this->pkey = $key['Column_name'];
		}
		
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