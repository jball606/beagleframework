<?php
/**
 * This is an example of a view.  This was called from the user class
 * and I passed the user class into it to get any data I needed
 * If done right, you should only be putting view logic in here.
 * 
 * @example /lib/views/edituser.php
 * 
 * @author Jason Ball
 * @package Beagleframework_Examples
 * @subpackage Views
 */
//Put all data into the record class
$record = $this->getUserData('all');

?>
<div class="centerdiv">
<script type="text/javascript">
/* Javascript control for the user page */
var user = new userclass();
</script>


<? //Example of how to use the beagle form validate javacript, this is the list of pages to check ?>
<div id="user_req_div" style="display:none">first_name;last_name;email;start_date</div>
<input style="float:right" type="button" value="Submit" onclick="user.verify(); return false;"/>
<div style="float:left">
<? //get value will check the array to see if the element is there, if not you get a blank, if populated it will ouput the data.  No warnings with this ?>
	<h1 class="pageheader">Edit User: <?=getValue($record,'first_name');?> <?=getValue($record,'last_name');?></h1>
	<? 
	if(isset($_GET['add']))
	{ ?><ul class="erroralert"><li>Record Updated</li></ul><? }
	else 
	{ ?><ul class="error" id="error"></ul><?  }
	?>
</div>

<div style="margin-bottom:10px" class="clearfloat"></div>


<div class="dblock rightcol">
	<div class="dblock_header">Access Information</div>
		<table class="form">
			<tr>
				<td><label for="start_date" id="start_date_title">Start Date </label><span class="alert">*</span></td>
				<td><input type="text" id="start_date" name="user[start_date]" value="<? (getDateValue($record,'start_date') != '') ? print(getDateValue($record,'start_date')) : print(date("m/d/Y"));?>"/></td>
			</tr>
			<tr>
				<td>End Date</td>
				<td><input type="text" id="end_date" name="user[end_date]" value="<?=getDateValue($record,'end_date');?>"/></td>
			</tr>
			<script type="text/javascript">
			addcal('start_date');
			addcal('end_date');
			</script>
			
		</table>
	
</div>

<div class="dblock leftcol">
	<div class="dblock_header">User Information</div>
	
	<table class="form">
		<tr>
			<td><label for="first_name" id="first_name_title">First Name </label><span class="alert">*</span></td>
			<td><input type="text" class="longfield" name="user[first_name]" id="first_name" value="<?=getValue($record,'first_name');?>"/></td>
		</tr>
		<tr>
			<td><label for="last_name" id="last_name_title">Last Name </label><span class="alert">*</span></td>
			<td><input type="text" class="longfield" name="user[last_name]" id="last_name" value="<?=getValue($record,'last_name');?>"/></td>
		</tr>
		<tr>
			<td><label for="passwd" id="passwd_title">Password </label><span class="alert">*</span></td>
			<td><input type="passwd" class="longfield" name="user[passwd]" id="passwd" value=""/></td>
		</tr>
		<tr>
			<td><label for="email" id="email_title">Email </label><span class="alert">*</span></td>
			<td><input type="text" class="longfield" name="user[email]" id="email" value="<?=getValue($record,'email');?>"/></td>
		</tr>
		<tr id="email_error_tr" style="display:none">
			<td> </td>
			<td><ul class="error" id="email_error"></ul></td>
		</tr>
			
		
		
	</table>
</div>
