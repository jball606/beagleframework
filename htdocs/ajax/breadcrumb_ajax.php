<?php
include_once("beaglecrminc.php");

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
			$GLOBALS['CONVERT_UBER'] = $info['uber_parent'];
			breadcrumbclass::resetToId($info['bcid'],$info['uber_parent']);
			$BC = breadcrumbclass::getLastBC($info['uber_parent']);
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