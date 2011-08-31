<?php
/**
 * This is an example of the control page.  This is the page the user will actually go to. 
 * Use this page to include javascripts and initiallize any classes or controllers you want.  
 * Classes are for single data objects while controllers are for group of data objects  
 
 * @example www.example.com/userlist.php
 * @author Jason Ball
 * @package Beagleframework_Examples
 * @subpackage HTML Controller
 */

//I use this for the page name
$pagename = "User List";
//Left side menu
$menulist = 1;
include("php_top.php");

//This is used to initiallize the parent class/controller for this specific page.  If it already load the previous object into the $UC variable
if(!$UC = breadcrumbclass::getLastBCSession('usersearch'))
{
	$UC = new users_controller();

}
//Show the page chain
breadcrumbclass::showBcChain();
?>
<h1><?=$pagename;?></h1>

<ul class="image">
	<li class="person"><a href="edituser.php">Add New User</a></li>

</ul>
<? // The beagleResults Javascript will do all that sorting, page manipulation that you want to do to a reasult list ?>
<script type="text/javascript" src="/js/beaglejs/beagleResults.js"></script>
<script type="text/javascript">
<!--
var usersearch = new beagleResults({resultdiv:'userlist',search:'usersearch'});

usersearch.userFunction = function(field,user_id) 
			{ 
				window.location.href="edituser.php?user_id="+user_id;
			};

//-->
</script>

<div id="userlist">
<? // Acually show the results page from the user_controller object?>
<?=$UC->showResultsPage(array('lib'=>'usersearch'));?>
</div>

<? 
//Store the class into the page holding variable
breadcrumbclass::storeBcSession('usersearch',$UC); 
include("php_bottom.php");
?>