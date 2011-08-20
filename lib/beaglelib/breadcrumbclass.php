<?php
include("beaglebase.php");
class breadcrumbclass extends beaglebase
{
	private $breadcrumb_id = false;
	private $url = false;
	private $session = array();
	private $name = false;
	private $parent = false;
	private $child = false;
	
	
	public function __construct($name)
	{
		$this->url = "http://".$_SERVER['HTTP_HOST']."/".$_SERVER['PHP_SELF'];
		$this->url = str_replace("/htdocs/", "", $this->url);
		if(!$this->lastIsNow($name,$this->url))
		{
			
			if($_SERVER['QUERY_STRING'] != "")
			{
				$this->url .= "?".$_SERVER['QUERY_STRING'];
			}
			
			
			$this->name = $name;
			$this->breadcrumb_id = rand(10000,99999);
			
			$this->getParent();
			$this->storeBC();
		}
	}
	
	public function __sleep()
	{
		return array_keys(get_object_vars($this));
	}
	
	public function __wakeup()
	{
		
	}
	
	public function getBcName()
	{
		return $this->name;
	}
	
	public function getBcURL()
	{
		$find = array();
		$replace = array();
		
		$find[] = "pcrumb=1&";
		$replace[] = "";
		$find[] = "pcrumb=1";
		$replace = "";
		
		$tmp = str_replace($find,$replace,$this->url);
		
		return $tmp;
	}
	
	public function getBcId()
	{
		return $this->breadcrumb_id;
	}
	
	public function setChild($child)
	{
		$this->child = $child;
	}
	
	public static function storeBCSession($search, $session)
	{
		$tab = breadcrumbclass::getLastBC();
		if($tab !== false)
		{
			$tab->saveSession($search, clone($session));
		}
		
		
	}
	
	public static function resetToId($id)
	{
		if(is_numeric($id) && isset($_SESSION['breadcrumbs'][$id]))
		{
			$bc= $_SESSION['breadcrumbs'];
			$rev = array_reverse($bc,true);
			
			$keep = false;
			$keepers = array();
			foreach($rev as $k => $i)
			{	
				if($k == $id)
				{
					$keep = true;
				}
				
				if($keep == true)
				{
					$keepers[$k] = $i;
				}
			}
			
			$final = array_reverse($keepers,true);
			
			$_SESSION['breadcrumbs'] = $final;
		}
		
		
	}
	
	public static function showBcChain()
	{
		if(isset($_SESSION['breadcrumbs']))
		{
			$tmp = $_SESSION['breadcrumbs'];
			$lnk = array();
			
			foreach($tmp as $k => $i)
			{
				$tab = unserialize($i);
				if(is_numeric($tab->getBcId()))
				{
					$lnk[] = array('id'=>$tab->getBcId(),'name'=>$tab->getBcName());
				}
				
			}
		
			$B = new beaglebase();
			print $B->showTemplate(getView('breadcrumbs.php','beagleviews'),$lnk);
		}
		else 
		{
			print("Breadcrumbs are broken");
		}
		
	}
	
	public static function getLastBC()
	{
		if(!isset($_SESSION['breadcrumbs']))
		{
			return false;
		}
		else if(!is_array($_SESSION['breadcrumbs']))
		{
			$tmp = $_SESSION['breadcrumbs'];
			$tab = unseraize($tmp);
			if(is_object($tab))
			{
				return $tab;
			}
			else 
			{
				return false;
			}
		}
		else 	
		{
			
			$tmp = $_SESSION['breadcrumbs'];
			
			$i = end($tmp);
			$tab = unserialize($i);
			return $tab;
		}
			
		
	}
	
	private function lastIsNow($name,$url)
	{
		$tab = $this->getLastBC();
		
		if($tab === false)
		{
			return false;
		}
		
		$n = $tab->getBcName();
		$u = $this->getBcURL();
		if($n == $name && $u == $url)
		{
			return true;
		}
		
		return false;
				
		
	}
	
	public static function clearLastBC()
	{
		if(isset($_SESSION['breadcrumbs']) && is_array($_SESSION['breadcrumbs']) && count($_SESSION['breadcrumbs'])>0)
		{
			$tmp = $_SESSION['breadcrumbs'];
			$keys = array_keys($tmp);
			$x = count($keys)-1;
			
			unset($tmp[$keys[$x]]);
			$_SESSION['breadcrumbs'] = $tmp;
		}
	}
	
	public static function restoreBCSession($search="")
	{
		$tmp = breadcrumbclass::getLastBC();
		if($tmp === false)
		{
			return false;
		}

		return $tmp->getBcSession($search);
		
	}
	
	public function getBcSession($search)
	{
		if(!isset($this->session[$search]))
		{
			return false;
		}
		
		return $this->session[$search];
	}
	
	public static function getLastBCSession($search)
	{
		$tmp = breadcrumbclass::getLastBC();
		if($tmp !== false)
		{
			return $tmp->getBcSession($search);
		}

		return false;
	}

	public function saveSession($search, $session)
	{
		$this->session[$search] = $session;
		$this->storeBC();	
	}
	
	/** 
	 * For Testing
	 * @param unknown_type $integer
	 */
	public function setBcId($id)
	{
		$this->breadcrumb_id = $id;
		
	}
	
	public function hasSession()
	{
		if($this->session !== false)
		{
			return true;
		}
		
		return false;
	}
	
	public function storeBC()
	{
		if(!isset($_SESSION['breadcrumbs']))
		{
			$_SESSION['breadcrumbs'][$this->breadcrumb_id] = serialize(clone($this));
		}
		else if(!is_array($_SESSION['breadcrumbs']))
		{
			$tmp = $_SESSION['breadcrumbs'];
			if(is_object($tmp))
			{
				$junk[$tmp->getBcId()] = $tmp;
			}
			
			$junk[$this->breadcrumb_id] = serialize(clone($this));
			$_SESSION['breadcrumbs'] = $junk;
		}
		else 	
		{
			$tmp = $_SESSION['breadcrumbs'];
			
			
			$tmp[$this->breadcrumb_id] = serialize(clone($this));
			
			
			$_SESSION['breadcrumbs'] = $tmp;
			
			
		}
		
	}
	
	
	private function getParent()
	{
		$tab = $this->getLastBC();
		
		if($tab === false)
		{
			$this->parent = false;
		}
		else 
		{
			$this->parent = $tab->getBcId();
			$tab->setChild($this->breadcrumb_id);
		}
	}
}
?>