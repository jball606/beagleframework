<?php
include_once("config/systemsetup.php");

if(strpos($_SERVER['REQUEST_URI'],".php") !== false)
{
	$tmp = explode("?", $_SERVER['REQUEST_URI']);
	include_once(__SYSTEM_ROOT__."/htdocs/".$tmp[0]);
	exit;
}


$var = explode("/",$_SERVER['REQUEST_URI']);

foreach($var as $k => $i)
{
	if(trim($i) == "")
	{
		unset($var[$k]);
	}
}

if(count($var) == 0)
{
	include(__SYSTEM_ROOT__."/htdocs/index.php");
	exit;
}

	unset($var[0]);
	$parts = array();
	foreach($var as $i)
	{
		$parts[] = $i;
	}
	
	$page = "";
	$junk = array();
	
	$page_index = getPageIndex($parts,__SYSTEM_ROOT__."/htdocs/");
	
	if($page_index === false )
	{
		$newparts = findTheIndex($parts);
		if(isPopArray($newparts))
		{
			$parts = $newparts['parts'];
			$page_index = $newparts['page_index'];
			
		}
		else 
		{
			goto404();
		}
	}
	
	$var = array();
	for($a=$page_index+1;$a<count($parts);$a++)
	{
		if(!isset($parts[$a+1]))
		{
			$var[$parts[$a]] = "";
		}
		else 
		{
			$var[$parts[$a]] = $parts[$a+1];
		}
		$a++;
	}
	
	if(count($parts) % 2 && $page_index != (count($parts)-1))
	{
		$parts[] = "";
	}
	
	$query = array();
	if($page_index !== false)
	{
		$url = "http://".$_SERVER["SERVER_NAME"];
		
		for($a = 0; $a<=$page_index;$a++)
		{
			
			if(trim($parts[$a]) != "")
			{
				$page .= "/".$parts[$a];
				$url .= "/".$parts[$a];
			}
		}
	
		if(isPopArray($var))
		{
			$_GET = $var;
		}
		
		if(count($junk)>0)
		{
			$url .= ".php?".implode("&",$junk);
		}
		else 
		{
			$url .= ".php";
		}

		include_once(__SYSTEM_ROOT__."/htdocs/".$page.".php");
	}


function findTheIndex($array)
{
	$start = __SYSTEM_ROOT__."/htdocs";
	$ni = 0;
	foreach($array as $k => $i)
	{
		$start .= "/".$i;
		
		$x = getPageIndex(array('index'), $start);
		if(isSetNum($x))
		{
			$ni++;	
			$tmp = array();
			foreach($array as $vk => $v)
			{
				if($vk < $ni || $vk > $ni)
				{
					$tmp[] =$v;
				}
				else 
				{
					$tmp[] = "index";
					$tmp[] = $v;
				}
			} 
			if(count($tmp) == $ni)
			{
				$tmp[] = 'index';
			}
			return array('parts'=>$tmp,'page_index'=>$ni);
		}
		$ni++;
	}
	
	return false;
}

function goto404()
{
	print("You hit the 404 message, whoops");
	exit;	
	
}
function getPageIndex($array,$start)
{
	foreach($array as $k => $i)
	{
		if(trim($i) != "")
		{
			if(file_exists($start."/".$i.".php"))
			{
				return $k;
			}
			else 
			{
				$start = $start ."/".$i;	
			}
			
		}
	}
	return false;
}
?>