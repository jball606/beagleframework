<?php
/**
 * @package beagleframework
 * @subpackage helpers
 * @author jball
 *
 */
class systemaccessclass extends beagledbclass
{
	protected $access_key = false;
	
	
	public function getAccessData($key)
	{
		$this->loadDB();
		if($this->access_key != false && is_numeric($key))
		{
			$tmp['userlist'] = 0;
			$tmp['grouplist'] = 0;
			$tmp['include'] = 1;
			
			$SQL = "Select array_to_string(array(select upid from ".$this->table." where ".$this->access_key." = ".$key."),',');";
			
			if($row = $this->db->getOne($SQL))
			{
				$tmp['userlist'] = $row;
			}
			
			$SQL = "Select array_to_string(array(select group_id from ".$this->table." where ".$this->access_key." = ".$key."),',');";
			if($row = $this->db->getOne($SQL))
			{
				$tmp['grouplist'] = $row;
			}
			
			$SQL = "Select gl_include_id from ".$this->table." where ".$this->access_key." = ".$key." limit 1;";
			$tmp['include'] = $this->db->getOne($SQL);
			
			
			return $tmp;
		}
		
		return false;
		
	}

	public function saveAccessData($in_args)
	{
		
		$args = defaultArgs($in_args,array('accessgroups'=>false,
											'accessusers'=>false,
											'accessinclude'=>1,
											'key'=>false,
											'client_id'=>false));
		
		$this->loadDB();
		if($args['client_id'] == false)
		{
			$args['client_id'] = $_SESSION['client_id'];
		}
		
		if($this->access_key != false && is_numeric($args['key']))
		{
		
			if(isPopArray($args['accessusers']))
				{
		
					$tmp = array();
					foreach($args['accessusers'] as $i)
					{
						if(is_numeric($i))
						{
							$tmp[] = $i;
						}
					}
					
					if(isPopArray($tmp))
					{
						$SQL = "insert into ".$this->table." (".$this->access_key.",gl_include_id,upid)
								select ".$args['key'].",".$args['accessinclude'].",upid
									from client_users where client_id = ".$args['client_id']."
									and (end_date is null or end_date <= now()) and upid in (".implode(",",$tmp).");";
		
						$this->db->query($SQL);
					}
				}
				if(isPopArray($args['accessgroups']))
				{
					$tmp = array();
					foreach($args['accessgroups'] as $i)
					{
						if(is_numeric($i))
						{
							$tmp[] = $i;
						}
					}
					if(isPopArray($tmp))
					{
						$SQL = "insert into ".$this->table." (".$this->access_key.",gl_include_id,group_id)
								select ".$args['key'].",".$args['accessinclude'].",group_id
									from groups where client_id = ".$args['client_id']."
									and active is null and group_id in (".implode(",",$tmp).");";
						$this->db->query($SQL);	
					}
				}	
		}
		
	}
}