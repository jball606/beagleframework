<?php
/**
 * This is the user model, a good example of how to make models in beagle framework
 *
 * The user model is a perfect example of how to create a model in beagle framework.  
 * You have to extend the beagle db class and tell the beagledbclass your table name,
 * your parent key, and if you have auditing fields.  You can set the name of the auditing 
 * fields in the /lib/config/systemsetup.php file.  Auditing fields are named created_by, created_date, modified_by, modified_date .
 * You can change the created/modified nameing in the systemsetup.php file.
 * 
 * 
 * 
 * @author Jason Ball
* @package Beagleframework Examples
 * @subpackage Models
 * 
 */
class users extends beagledbclass
{
	//Table Name
	protected $table = "users";
	//Primary Key
	protected $pkey = "user_id";
	//Do you want Auditing
	protected $auditing = true;
	
	//This array will do background verification for you
	protected $valid_fields = array('end_date'=>array('type'=>'date',
														'preset'=> '',
														'output'=>'Y-m-d'),
									
									'start_date'=>array('type'=>'date',
														'preset'=> '',
														'output'=>'Y-m-d'),
									'email'=>array('type'=>'email',
													'null'=>false),
									
									'first_name'=>array('type'=>'varchar',
																'size'=>255,
																'null'=>false),
									
									'last_name'=>array('type'=>'varchar',
														'size'=>255,
														'null'=>false),
	
									);
	
	public function __construct()
	{
		//You have to set any fields that required more then a static string in the construct of the model
		$this->valid_fields['start_date']['preset'] = date("Y-m-d G:i:s");
	}
}
?>