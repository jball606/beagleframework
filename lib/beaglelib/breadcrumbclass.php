<?php
include("beaglebase.php");
/**
 * The Breadcrumb class saves page specific data
 * 
 * The Bread crumb class does more then just make breadcrumbs for a page,
 * it also stores page specific data so that if you go back to a page, you don't
 * loose anything
 * 
 * 
 * @author Jason Ball
 * @copyright 05/01/2011
 \* @package Beagleframework
 * 
 *
 */
class breadcrumbclass extends beaglebase
{
	private $breadcrumb_id = false;
	private $url = false;
	private $session = array();
	private $name = false;
	private $parent = false;
	private $child = false;
	private $uber_parent = false;
	
	public function __construct($name)
	{
		$this->url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
		$this->url = str_replace("/htdocs/", "", $this->url);
		$this->url = breadcrumbclass::getUri();
		if(!$this->lastIsNow($name,$this->url))
		{
			if($_SERVER['QUERY_STRING'] != "")
			{
		//		$this->url .= "?".$_SERVER['QUERY_STRING'];
			}
			
			
			$this->name = $name;
			$this->breadcrumb_id = rand(10000,99999);

			$this->getParent();
			
		

			$this->storeBC();
			print('<script type="text/javascript">'."\n var breadcrumbid = ".$this->uber_parent."; \n </script>\n");
			$_SESSION[$this->getUri()] = $this->uber_parent;
		}
		else 
		{
			$tab = $this->getLastBC();
			print('<script type="text/javascript">'."\n var breadcrumbid = ".$tab->getUberParent()."; \n </script>\n");
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
		return $this->url;
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
	
	public function getUberParent()
	{
		return $this->uber_parent;
	}
	
	public function setChild($child)
	{
		$this->child = $child;
	}
	
	/**
	 * Store classes that you will use for this page
	 * @param string $search = array element
	 * @param object $session = class you want to save
	 * 
	 * @author Jason Ball 
	 */
	public static function storeBCSession($search, $session)
	{
		$tab = breadcrumbclass::getLastBC();
		if($tab !== false)
		{
			$tab->saveSession($search, clone($session));
		}
		
		
	}
	
	public static function resetUberParent($name="", $uber_parent="")
	{
		$uber_parent = breadcrumbclass::uberParentFind($uber_parent);
		
		if(isset($_SESSION['breadcrumbs'][$uber_parent]))
		{
			unset($_SESSION['breadcrumbs'][$uber_parent]);
		}
		foreach($_SESSION as $k => $i)
		{
			if($i == $uber_parent)
			{
				unset($_SESSION[$k]);
			}
		}
		//$B = new breadcrumbclass($name);
	}
	
	public static function resetToId($id,$uber_parent)
	{
		if(is_numeric($id) && isset($_SESSION['breadcrumbs'][$uber_parent][$id]))
		{
			$bc= $_SESSION['breadcrumbs'][$uber_parent];
		
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
			$_SESSION['breadcrumbs'][$uber_parent] = $final;
		}
		
		
	}
	
	public static function showBcChain($uber_parent="")
	{
	
		$uber_parent = breadcrumbclass::uberParentFind($uber_parent);
		if(isset($_SESSION['breadcrumbs'][$uber_parent]))
		{
			$tmp = $_SESSION['breadcrumbs'][$uber_parent];
			$lnk = array();
		
			foreach($tmp as $k => $i)
			{
				$tab = unserialize($i);
				if(is_numeric($tab->getBcId()))
				{
					$lnk[] = array('id'=>$tab->getBcId(),'name'=>$tab->getBcName(),'uber_parent'=>$tab->getUberParent());
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
	public static function getUri($withquery = true)
	{
	
		$url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		
		return $url;
	}
	
	public static function uberParentFind($uber_parent="")
	{
		if(!isSetNum($uber_parent))
		{
			if(trim($uber_parent) == "")
			{
				writeLog("SEARCH ");
				writeLog("REFERR ".$_SERVER['HTTP_REFERER']);
				if(isset($_SERVER['HTTP_REFERER']) && isset($_SESSION[$_SERVER['HTTP_REFERER']]))
				{
					writeLog("REFERR ".$_SERVER['HTTP_REFERER']);
					$uber_parent = $_SESSION[$_SERVER['HTTP_REFERER']];
				}
				elseif(isset($_SESSION[breadcrumbclass::getUri()]))
				{
					writeLog("URI ".breadcrumbclass::getUri());
					$uber_parent = $_SESSION[breadcrumbclass::getUri()];
				}
			}
			else 
			{
			//	print($_SERVER['HTTP_REFERER']); 
				return false;
			}
		}
		writeLog("UBER FOUND ".$uber_parent);
		return $uber_parent;
	}
	
	public static function getLastBC($uber_parent="")
	{
		$uber_parent = breadcrumbclass::uberParentFind($uber_parent);
			
		if(!isset($_SESSION['breadcrumbs']) || !isset($_SESSION['breadcrumbs'][$uber_parent]))
		{
			return false;
		}
		$session = $_SESSION['breadcrumbs'][$uber_parent];
		
		if(!is_array($session))
		{
			$tmp = $session;
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
			
			$tmp = $session;
			
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
		if(isset($_SESSION['breadcrumbs'][$this->uber_parent]) && is_array($_SESSION['breadcrumbs'][$this->uber_parent]) && count($_SESSION['breadcrumbs'][$this->uber_parent])>0)
		{
			$tmp = $_SESSION['breadcrumbs'][$this->uber_parent];
			$keys = array_keys($tmp);
			$x = count($keys)-1;
			
			unset($tmp[$keys[$x]]);
			$_SESSION['breadcrumbs'][$this->uber_parent] = $tmp;
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
		if(!isset($_SESSION['breadcrumbs'][$this->uber_parent]))
		{
			$_SESSION['breadcrumbs'][$this->uber_parent][$this->breadcrumb_id] = serialize(clone($this));
	
		}
		else if(!is_array($_SESSION['breadcrumbs'][$this->uber_parent]))
		{
			$tmp = $_SESSION['breadcrumbs'][$this->uber_parent];
			if(is_object($tmp))
			{
				$junk[$tmp->getBcId()] = $tmp;
			}
			
			$junk[$this->breadcrumb_id] = serialize(clone($this));
			$_SESSION['breadcrumbs'][$this->uber_parent] = $junk;
		}
		else 	
		{
			$tmp = $_SESSION['breadcrumbs'][$this->uber_parent];
			
			
			$tmp[$this->breadcrumb_id] = serialize(clone($this));
			
			
			$_SESSION['breadcrumbs'][$this->uber_parent] = $tmp;
			
			
		}
		
	}
	
	
	private function getParent()
	{
		$tab = $this->getLastBC();
		
		if($tab === false)
		{
			$this->parent = false;
			$this->uber_parent = rand(1000,999999999999);
		}
		else 
		{
			$this->parent = $tab->getBcId();
			$this->uber_parent = $tab->getUberParent();
			
			$tab->setChild($this->breadcrumb_id);
		}
	}
}
?>