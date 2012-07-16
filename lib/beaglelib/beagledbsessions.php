<?php
/**
 * this Page is if you want to switch your session from a file base system to a DB based system
 * You need to include this file in the beagles setting page
 */


/**
 * DB SQL 
 * 
   CREATE TABLE sessions (
    id varchar(32) NOT NULL,
    access int(10) unsigned,
    data text,
    PRIMARY KEY (id)
    );
 */
 
class beagledbsessions extends beaglebase 
{
    protected $savePath;
    protected $sessionName;

    public function __construct() 
    {
    	if(!isset($GLOBALS['DB']))
    	{
    		return false;
    	}
    	
    	$this->loadSystemDB();
    	
    	//Make sure the table exist and if not, create it
    	if($this->db->getDBType() == "pgsql")
    	{
    		$resource = $this->db->getOne("SELECT relname FROM pg_class WHERE relname='sessions'");	
    		if($resource == "")
			{
					$SQL = 'CREATE TABLE public.sessions (
			    			id varchar(32) NOT NULL,
			    			access int4,
			    			data text,
			    			PRIMARY KEY (id)
			    			);';
				$this->db->query($SQL); 
			} 
    	}
    	else 
    	{
	    	$resource = $this->db->getOne("show tables like 'sessions'");
			
			if($resource == "")
			{
				$SQL = " CREATE TABLE sessions (
						    id varchar(32) NOT NULL,
						    access int(10) unsigned,
						    data text,
						    PRIMARY KEY (id)
						    );";
				$this->db->query($SQL); 
			} 
    	}
    	
        session_set_save_handler(
            					array($this, "open"),
            					array($this, "close"),
            					array($this, "read"),
            					array($this, "write"),
            					array($this, "destroy"),
            					array($this, "gc")
        						);

    	
		
    }

    public function open() 
    {
    //  return true;
    }

    public function close() 
    {
       $this->closeBase();
    }

    public function read($id) 
    {
    	$SQL = "SELECT data
				FROM sessions
				WHERE id = '".$this->db->escape($id)."'";

    	$record = $this->db->getOne($SQL);

		return $record;
	
	}
    	
    public function write($id, $data) 
    {
    	$access = time();
    	
      	$id = $this->db->escape($id);
		$data = $this->db->escape($data);
		if($this->db->getDBType() == "pgsql")
		{
			$SQL = "Delete from sessions where id = '".$id."';";
			$this->db->query($SQL);

			$SQL = "insert into sessions values ('".$id."','".$access."','".$data."');";
			$this->db->query($SQL);
		}
		else 
		{
				$SQL = "REPLACE
				INTO sessions
				VALUES ('$id', '$access', '$data')";
		 
			$this->db->query($SQL);
		}
		
    }

    public function destroy($id) 
    {
    	$SQL = "DELETE
				FROM sessions
				WHERE id = '".$this->db->escape($id)."'";
		
		$this->db->query($SQL);
    }

    public function gc($maxlifetime) 
    {
    	
		$old = time() - $maxlifetime;
		$old = $this->db->escape($old);
 
		$SQL = "DELETE
				FROM sessions
				WHERE access < '$old'";
 
		
		$this->db->query($SQL);
		
    }
}
?>