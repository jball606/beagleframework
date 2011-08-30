<?php
/**
 * In Beagleframework, a class is just an object if one data type
 * 
 * In this example, the class is a single user.  User "Bob" is the controlling 
 * item of this class. All the methods get the attributes for Bob.  This is more
 * of the classical sence of Object Oriented programming then the PHP implementation of it.
 * 
 * @author Jason Ball
 * @package Beagleframework Examples
 * @subpackage Classes
 *
 */
class userclass extends beaglebase
{
	private $userdata = array();
	
	/**
	 * This is the implementation of the magic __wakeup function
	 * 
	 * This is the reason for doing things MVCC.  YOu can store and restore a class from page to page
	 * giving you more security because you don't have to pass everything int he URL bar
	 * 
	 */
	public function __wakeup()
	{
		parent::__wakeup();	
		$this->loadUser($this->user_id);
	}
	
	public function __construct($in_args = array())
	{
		$args = defaultArgs($in_args,array('user_id'=>false));
		
		
		$this->loadSystemDB();
		
		if(is_numeric($args['user_id']))
		{
			$this->loadUser($args['user_id']);
		}
		
	}
	
	/**
	 * Add User to system
	 * @param array $userdata
	 * @return string data or user id
	 * @author Jason Ball
	 * @copyright 2011-08-02
	 */
	private function addUser($userdata)
	{
		$U = new users();
		
		if(!isValidPopDate($userdata['start_date']))
		{
			$userdata['start_date'] = $U->getPresets(array('start_date'));
		}
		
		$us = $U->formatData($userdata);
		
		$user_id = $U->add($us);
		
		return $user_id;
	}
	
	/**
	 * Check for Email Duplacates
	 * @param string $email
	 * @return false or int
	 * @author Jason Ball
	 * @copyright 2011-08-01
	 */
	public function checkEmail($email)
	{
		$U = new users();
		$user_id = $U->getOne(array('lower(email)'=>$email,
									'user_id !'=>$this->user_id),array('fields'=>array('user_id')));

		
		return $user_id;
		
	}
	
	/**
	 * 
	 * Load internal user data
	 * @param integer $upid
	 * @author Jason Ball
	 * @copyright 2011-07-30
	 */
	private function loadUser($upid)
	{
		$U = new users();
		$row = $U->getOne(array('user_id'=>$upid));
		if(!isSetNum($row['user_id']))
		{
			$this->error = "Invalid User";
			return false;
		}

		$this->user_id = $row['user_id'];
		$this->userdata = $row;
		$this->loadUserGroups();
		$this->loadUserMenu();
	}
	
	public function getUserId()
	{
		return $this->getUserData('user_id');
	}
	
	/**
	 * Most classes have an array for data storage, this implements the 
	 * parent method getClassData with you passing the specific 
	 * Classes variables
	 *
	 * @param array $item
	 * @return mixed array, string, false
	 * @author Jason Ball
	 */
	public function getUserData($item="")
	{
		return $this->getClassData($this->userdata,$item);
	}
	
	public function getUserTabMenu()
	{
		return $this->userdata['menudata']->getMainMenu();	
	}
	
	public function getMenuList($menu)
	{
		if(is_numeric($menu))
		{
			$result = $this->userdata['menudata']->getMenuList($menu);
			return $this->showTemplate(getView('leftmenu.php','general'),$result);
		}
		
	}
	
	public function isAdmin()
	{
		if(isset($this->userdata['groups'][1]))
		{
			return true;
		}

		return false;
	}
	
	public function saveUser($in_args = array())
	{
		$args = defaultArgs($in_args, array('user'=>false
		
											));

		if(isPopArray($args['user']))
		{
			$U = new users();
			
			if(!is_numeric($this->user_id))
			{
				$user_id = $this->addUser($args['user']);
				
				if($user_id == false)
				{
					return $U->error;
				}
				
				$this->user_id = $user_id;
			}
			else 
			{
				$ud = $U->formatData($args['user']);
				$U->update(array('user_id'=>$this->user_id),$ud);
				
				
			}
			$this->loadUser($this->user_id);
			
		}
		return $this->user_id;
	}
	
	private function loadUserGroups()
	{
		$SQL = "Select g.group_id, g.group_name
		from groups g
			inner join group_users gu
				on g.group_id = gu.group_id
		where (g.end_date is null or g.end_date >= now())
		and gu.user_id = ".$this->user_id;
		  
		
		$tmp = array();
		
		$result = $this->db->query($SQL);
		while($row = $result->fetchRow())
		{
			$tmp[$row['group_id']] = $row['group_name'];
		}
		
		$this->userdata['groups'] = $tmp;
	}

	private function loadUserMenu()
	{
		$this->userdata['menudata'] = new menusystemclass(array('user_id'=>$this->user_id,'admin'=>$this->isAdmin()));
		
	}

	/**
	 * This uses the showTemplate method so that you can show your view.  You can pass the class to that view to access more methods  
	 * 
	 * If an error has been populated, your view will not show but the error message will.  I use the getView function to get the dynamic
	 * name of views but you could do this manually if you want
	 * 
	 * @param void
	 * @return html
	 */
	public function showEditUser()
	{
		return $this->showTemplate(getView('edituser.php','systemadmin'),$this);
		
		
	}
}
?>