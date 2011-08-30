<?php
/**
 * Controllers in Beagle Framework are just classes that control a collection of 
 * data on a specific topic.  These are usually used for search and result type pages
 * that don't control a specific element of data but a group of elements
 * 
 * 
 * @author Jason Ball
 * @package Beagleframework_Examples
 * @subpackage Controllers
 *
 */
class users_controller extends beagleresultclass
{
	
	/**
	 * This is an exmple of how to implement the cleanName abstract methed in beagleresultclass
	 */
	protected function cleanName($name)
	{
		//If you want to just have the name from the database be used but look clean, us this method
		return $this->standardTitle($name);
	}
	
	/**
	 * This is an example of how to implement the loadSubWhere method.  This allows you to controll the letter menu
	 */
	public function loadSubWhere($where)
	{
		$array['users']['first_name'] = $where;
		$this->loadLetterNav($array);
	}

	/**
	 * Example of how to implement the run_Search method.  This method is where you put the from clause of your SQL statement
	 */
	protected function run_Search($first=0,$limit=20,$excel=false,$all=false)
	{
		$SQL_F = " from users ";	
		
		return $this->executSearch(array('first'=>$first,
											'limit'=>$limit,
											'excel'=>$excel,
											'SQL_F'=>$SQL_F,
											'key'=>array('id'=>'users.user_id','name'=>'users.user_id','sqlkey'=>'user_id'),
											'all'=>$all,
											'printsql'=>false));
		
	}
	
	/**
	 * Example of how to implament the abstract showResultPage method.  The search_ajax.php page will autoexecute this.
	 * I used switch statments and the lib (javascript controlling library) to use more then one of these methods 
	 */
	public function showResultsPage($in_args = array())
	{
		$args = defaultArgs($in_args,array('first'=>false,
											'limit'=>false,
											'orderby'=>false,
											'orderdir'=>false,
											'lib'=>'search',
										));
		
		$this->viewitems['users.first_name'] = 'users.first_name';
		$this->viewitems['users.last_name'] = 'users.last_name';
		$this->viewitems['users.email'] = 'users.email';
		$this->viewitems['users.start_date'] = 'users.start_date';
		$this->viewitems['users.end_date'] = 'users.end_date';
		
		return $this->runResultPage(array('first'=>$args['first'],
											'limit'=>$args['limit'],
											'orderby'=>$args['orderby'],
											'orderdir'=>$args['orderdir'],
											'dates'=>array('start_date'=>"m/d/Y", 'end_date'=>'m/d/Y'),
											'edit_pencil'=>array('key'=>'user_id'),
											'lib'=>$args['lib'],
											'lettermenu'=>array('col'=>'[users][first_name]','name'=>'Users First Name','key'=>'first_name'),
										 								
		));
		
		
		
	}
	
}
?>