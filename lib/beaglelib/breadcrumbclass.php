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
 * @package Beagleframework
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
	
	public function __construct($name="")
	{
		$this->url = self::getUrl();
		$name = $this->getPageName($name);
		
		if(!$this->lastIsNow($name,$this->url))
		{
			$this->name = $name;
			$this->breadcrumb_id = rand(10000,99999);

			$this->getParent();
			
		

			$this->storeBC();
			print('<script type="text/javascript">'."\n var breadcrumbid = ".$this->uber_parent."; \n </script>\n");
			$_SESSION[$this->getUrl()] = $this->uber_parent;
		}
		else 
		{
			$tab = $this->getLastBC();
			print('<script type="text/javascript">'."\n var breadcrumbid = ".$tab->getUberParent()."; \n </script>\n");
		}
	}
	
	public function __sleep()
	{
		if(isset($this))
		{
			$up = $this->uber_parent;
		}
		else
		{
			$up = self::findUberParent();
		}
		
		$_SESSION['breadcrumb_'.$up."_TTL"] = time();
		return parent::__sleep();
	}
	
	/**
	 * This method is used to clear out all the breadcrumbs for a particular window if you have the reset key in your url
	 * @param string $pagename
	 * @return void
	 * @author Jason Ball
	 */
	public static function cleanupBreadCrumbs($pagename="")
	{
		$pagename = self::getPageName($pagename);
		if(isset($_GET['pcrumb']) && !isset($_GET['frombc']))
		{
			self::resetUberParent($pagename);
			
			$old = self::getOldBreadCrumbs();
			if(isPopArray($old))
			{
				foreach($old as $i)
				{	
					if(isset($_SESSION['breadcrumbs'][$i]))
					{
						unset($_SESSION['breadcrumbs'][$i]);
					}
					unset($_SESSION['breadcrumb_'.$i.'_TTL']);
					foreach($_SESSION as $k => $v)
					{
						if($v == $i)
						{
							unset($_SESSION[$k]);
						}
					}
					
				}
			}
		}
		
	}
	
	/**
	 * This method will clear out the last breadcrumb in a string of breadcrumbs no matter where you are at in the chain
	 * @author Jason Ball
	 */
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
	
	/**
	 * This Method is used to make sure any breadcrumb tags we use are removed for normal operations
	 * 
	 * @param string $url
	 * @return string
	 * @author Jason Ball
	 */
	private static function cleanUrl($url)
	{
		if(strpos($url,'frombc=true') !== false)
		{
			$url = str_replace("&frombc=true","",$url);
		}	
		return $url;
	}
	
	/**
	 * Sometimes you need to use a different url then what you started with
	 * Enter description here ...
	 * @param unknown_type $newurl
	 */
	public function convertUrl($newurl)
	{
		$this->url = $newurl;
		
		$this->storeBC();
		$newuberparent = $this->generateUberParent();
		if(isPopArray($_SESSION['breadcrumbs'][$this->uber_parent]))
		{
			$newbc = array();
			$tmp = $_SESSION['breadcrumbs'][$this->uber_parent];
		
			foreach($tmp as $k => $i)
			{
				$b = unserialize($i);
				$b->storeUberParent($newuberparent);
				$newbc[$k] = serialize($b);
			}
			
			$_SESSION['breadcrumbs'][$newuberparent] = $newbc;
		}
		$GLOBALS['CONVERT_UBER'] = $newuberparent;
		$_SESSION[$this->cleanUrl($this->url)] = $this->uber_parent;
		
	}
	
	/**
	 * This method helps us to find the uber parent.  The uber parent allows us to keep track of what window a user is in
	 * 
	 * @param string $uber_parent
	 * @return string/boolean
	 * @author Jason Ball
	 */
	private static function findUberParent($uber_parent="")
	{
		if(!isSetNum($uber_parent))
		{
			if(trim($uber_parent) == "")
			{
				global $CONVERT_UBER;
				if(isSetNum($CONVERT_UBER))
				{
					$uber_parent = $CONVERT_UBER;
				}
				elseif(isset($_SESSION[self::getUrl()]))
				{
					$uber_parent = $_SESSION[self::getUrl()];
				}
				elseif(isset($_SERVER['HTTP_REFERER']) && isset($_SESSION[self::cleanUrl($_SERVER['HTTP_REFERER'])]))
				{
					$uber_parent = $_SESSION[self::cleanUrl($_SERVER['HTTP_REFERER'])];
				}
				
			}
			else 
			{
				return false;
			}
		}
		return $uber_parent;
	}
	
	private function generateUberParent()
	{
		return  rand(1000,999999999999);
		
	}
	
	/**
	 * get Breadcrumb id
	 * @param void
	 * @return integer
	 * @author Jason Ball
	 */
	public function getBcId()
	{
		return $this->breadcrumb_id;
	}
	
	/**
	 * get breadcrumb name
	 * @param void
	 * @return string
	 * @author Jason Ball
	 */
	private function getBcName()
	{
		return $this->name;
	}
	
	public function getBcSession($search)
	{
		if(!isset($this->session[$search]))
		{
			return false;
		}
		
		return $this->session[$search];
	}
	
	public function getBcURL($urlcheck = false)
	{
		if(strpos($this->url,'pcrumb') !== false && $urlcheck == false)
		{
			return $this->url."&frombc=true";
		}
		
		return $this->url;
	}
	
	public static function getLastBC($uber_parent="")
	{
		$uber_parent = self::findUberParent($uber_parent);

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

	public static function getLastBCSession($search)
	{
		$tmp = breadcrumbclass::getLastBC();
		if($tmp !== false)
		{
			return $tmp->getBcSession($search);
		}

		return false;
	}
	
	private static function getOldBreadCrumbs()
	{
		$oldcrumbs = array();
		if(isPopArray($_SESSION['breadcrumb']) && count($_SESSION['breadcrumb']) == 1)
		{
			return $oldcrumbs;
		}
		
		foreach($_SESSION as $k => $i)
		{
			if(strpos($k,'breadcrumb') !== false && strpos($k,'TTL')!==false)
			{
				if($i < time() - 600)
				{
					$tmp = explode("_",$k);
					$oldcrumbs[] = $tmp[1];
				}
				
			}
			
		}
		
		return $oldcrumbs;
	}
	
	private static function getPageName($name="")
	{
		if($name == "")
		{
			$name = self::getUrl();
		}
		return $name;
	}
	
	private function getParent()
	{
		$tab = $this->getLastBC();
		
		if($tab === false)
		{
			$this->parent = false;
			$this->uber_parent = $this->generateUberParent();
		}
		else 
		{
			$this->parent = $tab->getBcId();
			$this->uber_parent = $tab->getUberParent();
			
			$tab->setChild($this->breadcrumb_id);
		}
	}
	
	public function getUberParent()
	{
		return $this->uber_parent;
	}
	
	private static function getUrl($withquery = true)
	{
	
		$url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		
		$url = self::cleanUrl($url);
		
		return $url;
	}

	public function hasSession()
	{
		if($this->session !== false)
		{
			return true;
		}
		
		return false;
	}
	
	private function lastIsNow($name,$url)
	{
		$tab = $this->getLastBC();
		
		if($tab === false)
		{
			return false;
		}
		$n = $tab->getBcName();
		$u = $this->getBcURL(true);

		if($n == $name && $u == $url)
		{
			return true;
		}
		return false;
				
		
	}
	
	public static function resetToId($id,$uber_parent)
	{
		if(is_numeric($id) && isset($_SESSION['breadcrumbs'][$uber_parent][$id]))
		{
			$bc= $_SESSION['breadcrumbs'][$uber_parent];
		
			$rev = array_reverse($bc,true);
			$keys = array_keys($bc);
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
			$keys = array_keys($final);
		
			$_SESSION['breadcrumbs'][$uber_parent] = $final;
		}
		
		
	}
	
	private static function resetUberParent($name="", $uber_parent="")
	{
		$uber_parent = self::findUberParent($uber_parent);
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
		
	}
	
	/**
	 * This mehtod allows us to restore any session class that was stored inside the breadcrumb
	 * 
	 * @param string $search
	 * @return object/false
	 * @author Jason Ball
	 */
	public static function restoreBCSession($search="")
	{
		$tmp = breadcrumbclass::getLastBC();
		if($tmp === false)
		{
			return false;
		}

		return $tmp->getBcSession($search);
		
	}
	
	/**
	 * This method allow you to store a session into the breadcrumb so you can keep it for later
	 * 
	 * @param string $obj_name
	 * @param object $obj
	 */
	public function saveSession($obj_name, $obj)
	{
		$this->session[$obj_name] = $obj;
		$this->storeBC();	
	}
	
	public function setChild($child)
	{
		$this->child = $child;
	}
	
	public static function showBcChain($uber_parent="")
	{
	
		$uber_parent = self::findUberParent($uber_parent);
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
			if(is_object($session))
			{
				$tab->saveSession($search, clone($session));
			}
			else 
			{
				$tab->saveSession($search,$session);
			}
		}
		
		
	}
	
	public function storeUberParent($uber_parent)
	{
		$this->uber_parent = $uber_parent;	
		
	}
	
}
?>