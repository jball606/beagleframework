<?php
include_once("config/systemsetup.php");

if(isset($_GET['id']))
{
	$info = $_GET;
}
else 
{
	$info = $_POST;
}


if(isset($info['id']))
{
	switch($info['id'])
	{
		case "breadcrumb":
		{
			breadcrumbclass::resetToId($info['bcid']);
			$BC = breadcrumbclass::getLastBC();
			print json_encode(array('url'=>$BC->getBcUrl()));
			break;
			
		}
		case "resettab":
		{
			breadcrumbclass::clearLastBC();
			break;		
		}
		
	}
	
}
?>				