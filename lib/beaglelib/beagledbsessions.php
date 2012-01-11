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
      return true;
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
 
		$SQL = "REPLACE
		INTO sessions
		VALUES ('$id', '$access', '$data')";
 
		return $this->db->query($SQL);
		
    }

    public function destroy($id) 
    {
		$SQL = "DELETE
				FROM sessions
				WHERE id = '".$this->db->escape($id)."'";
		
		return $this->db->query($SQL);
    }

    public function gc($maxlifetime) 
    {
		$old = time() - $maxlifetime;
		$old = $this->db->escape($old);
 
		$SQL = "DELETE
				FROM sessions
				WHERE access < '$old'";
 
		return $this->db->query($SQL);
    }
}
?>