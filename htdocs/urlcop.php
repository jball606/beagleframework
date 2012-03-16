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
	
		
		for($a=($page_index+1);$a<count($parts);$a++)
		{
			$_GET[$parts[$a]] = $parts[$a+1];
			$junk[] = $parts[$a]."=".$parts[$a+1];
			$a++;
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