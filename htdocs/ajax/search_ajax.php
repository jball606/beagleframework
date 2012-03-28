<?php
$ajax_page="Y";

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
	$search = 'search';
	if(isset($info['search']))
	{
		$search = $info['search'];
	}
			
	switch($info['id'])
	{
		case "singlecheck":
		{
			if($S = breadcrumbclass::restoreBcSession($search))
			{	
				$S->loadCheck($info['value'],$info['check']);
				
				breadcrumbclass::storeBcSession($search,$S);
			}
			break;		
		}
		case "allcheck":
		{
			if($S = breadcrumbclass::restoreBcSession($search))
			{	
				$S->setAllCheck($info['check']);
				
				breadcrumbclass::storeBcSession($search,$S);
			}
			break;	
				
		}
		case "searchresults":
		{

			$S = breadcrumbclass::restoreBcSession($search);
			
			
			if($S)
			{
				
				if(isset($info['specialwhere']))
				{
					$S->loadSubWhere($info['specialwhere']);
				}
				
				$arg = array('first'=>$info['first'],
												'limit'=>$info['limit'],
												'orderby'=>$info['orderby'],
												'orderdir'=>$info['orderdir'],
												'lib'=>$search,
							);
				if(isset($info['div']))
				{
					$arg['div'] = $info['div'];
				}
				
				print $S->showResultsPage($arg);
				
				
				
				breadcrumbclass::storeBcSession($search,$S);
			
			}
			else 	
			{
				print("<ul class='erroralert'><li>Invalid Search</li></ul>");
			}
		
			break;
		}
	}
}
?>