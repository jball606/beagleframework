<?php
/**
 * This class is used to create the results you see on screen
 * @author Jason Ball
 \* @package Beagleframework
 * 
 * @copyright 08/21/2011
 */
abstract class beagleResultClass extends beagleNavigationClass
{
	protected $editclass = array(); 
	
	/**
	 * This method will return a clean name of a DB filed name
	 * @param string $name
	 * @return string $name
	 * @author Jason Ball
	 * @copyright 2011-08-02
	 */
	protected function standardTitle($name)
	{
		if(strpos($name,'.')!==false)
		{
			$name = substr($name,strpos($name,'.')+1,strlen($name));
		}
		
		return ucwords(str_replace("_"," ",$name));
	}
	
	/**
	 * Abstract method needed to make sure result titles have a clean name.  Special to each controller.  
	 * You can make the function to just use standardTitle if you want
	 * @param string $name
	 * 
	 */
	abstract protected function cleanName($name);
	
	/**
	 * abstract method needed by each controller to pass seetings to runResultPage
	 * @see beagleResultClass::runResultPage()
	 */
	abstract public function showResultsPage();
	
	/**
	 * abstract method that is used to run a search with search criteria needed
	 * @see beagleSearchClass::executeSearch()
	 * pass these params $first=0,$limit=20,$excel=false,$all=false
	 */
	abstract protected function run_Search();
	
	/**
	 * This is the function that creates the result list based on passed criteria
	 * @param array $in_args <br/>
	 * <pre>
	 * in_args  = array( 
	 * first => 			false 		Start of result list
	 * limit =>	 			false, 		How many per page
	 * orderby =>			false,		Order field 
	 * orderdir	=>			false, 		Order Dir 1 (ASC) 2 (DESC)
	 * page	=>	'',				
	 * title =>				false, 		Title on header bar
	 * dates => 			array(),	Date fields that need formatting example 'dates'=>array('created_date'=>"m/d/Y"),
	 * link =>				array(), 	Link Array' exmpale array(0 => array('field'=>'email','key'=>'upid')),
	 * edit_pencil =>		array(),	array to create the edit pencil icon at the end of the row example: array('key'=>'utility_id')
	 * bottommenu =>		false, 		If you want links at the bottom, this is a simple include() function so pass only the url
	 * lib =>				'search',	Javascript class name that controls the JS result object
	 * sel =>				false,		Do you need select bcxes for the row? false or array(name,key) of box
	 * lettermenu =>		array(), 	Used to create the result row letter menu, array includes name = title and key = dbid
	 * showemptyresult =>	false,		If you want an empty result list instead of the (no records found) error message
	 * showperpage =>		true,		Show the how many records on that result you want to see
	 * showcount =>			true,		Show the total number of records from the search
	 * all =>				false,		Show all records, reality is it show the first 1000000
	 * extra =>				array(),
	 * editaccess =>		false, 		Used to give you the popup to select who can and can not see your information
	 * allowsort =>			true, 		Allow the user to sort a row
	 * hiddencols =>		array(),	array of columns of data in a result set that you have to have but don't want the user to see
	 * edit =>				false,		This flag is for telling the system that it should try to use the edit results system, pass the row's primary key 	
	 *  ) </pre>
	 *  
	 *  @author Jason Ball
	 */
	protected function runResultPage($in_args = array())
	{
		$args = defaultArgs($in_args,array('first'=>false, //Start of result list
											'limit'=>false, //How many per page
											'orderby'=>false, //Order field
											'orderdir'=>false, //Order Dir 1 (ASC) 2 (DESC)
											'page'=>'',
											'title'=>false, //Title on header bar
											'dates'=>array(),	//Date fields that need formatting example 'dates'=>array('created_date'=>"m/d/Y"),
											'link'=>array(), 	//Link Array' exmpale array(0 => array('field'=>'email','key'=>'upid')),
											'edit_pencil'=>array(),
											'bottommenu'=>false, //If you want links at the bottom
											'lib'=>'search',	//Javascript class name
											'sel'=>false,	//Do you need select bcxes for the row? false or array(name,key) of box
											'lettermenu'=>array(), //name = title and key = dbid
											'showemptyresult'=>false,
											'showperpage'=>true,
											'showcount'=>true,
											'extra'=>array(),
											'all'=>false,
											'editaccess'=>false, //Used to give you the popup to select who can and can not see your information
											'allowsort'=>true, //Allow the user to sort a row
											'hiddencols'=>array(),	
											'edit'=>false, //this flag is for telling the system that it should try to use the edit results system
											'return_result_array'=>false, //In case I want to only get the array and not the view
										));
											
											
											
		$this->processOrderNavArray($args);
		
		if(isPopArray($args['hiddencols']))
		{
			$tmp = array();
			foreach($args['hiddencols'] as $k => $i)
			{
				if(is_numeric($k))
				{
					$tmp[$i] = $i;
				}
				else 
				{
					$tmp[$k] = $i;
				}
				
			}
			$args['hiddencols'] = $tmp;
		}
		
		if($args['first'] === false && isset($this->location['first']))
		{
			$args['first'] = $this->location['first'];
		}
		elseif(!$args['first']) 
		{
			$args['first'] = 0;
		}
	
		if(!$args['limit'] && isset($this->location['limit']))
		{
			$args['limit'] = $this->location['limit'];
		}
		elseif(!$args['limit'] && $args['all'] == false) 
		{
			$args['limit'] = 10;
		}
		elseif($args['all'] == true)
		{
			$args['limit'] = '1000000';
		}
		
		$result = $this->run_Search($args['first'],$args['limit']);

		$result['title'] = $args['title'];
		$result['headers'] = $this->getHeaders($this->viewitems);
		$result['limit'] = $args['limit'];
		$result['editaccess'] = $args['editaccess'];
		$result['allowsort'] = $args['allowsort'];
		$result['hiddencols'] = $args['hiddencols'];
		
		$result['first'] = $args['first'];
		$result['dates'] = $args['dates'];
		if(isPopArray($args['edit_pencil']))
		{
			$result['edit_pencil'] = true;
			
		}
		$result['order'] = $this->order;
		$result['lib'] = $args['lib'];
		$result['showperpage'] = $args['showperpage'];
		$result['showcount'] = $args['showcount'];
		$result['showemptyresult'] = $args['showemptyresult'];
		
		if($args['edit'])
		{
			$result['editsystem'] = $this->editclass;
			$result['editkey'] = $args['edit'];
		}
		
		if(isset($args['lettermenu']['name']))
		{
			$result['lettermenu']['name'] = $args['lettermenu']['name'];
			$result['lettermenu']['list'] = $this->createLetterMenu($this->run_search($args['first'],$args['limit'],true,true),$args['lettermenu']['key']);
			$result['lettermenu']['sel'] = $this->letterval;
		}
		
		if($args['sel'] !== false)
		{
			$result['sel'] = $args['sel'];
			$result['check'] = $this->check;
		}
		$result['bottommenu'] = $args['bottommenu'];
		
		$result['orgdata'] = $result['records'];
		if(isPopArray($args['extra']))
		{
			foreach($args['extra'] as $k => $i)
			{
				$result[$k] = $i;
			}
		}
		
		$junk = array();
		
		if((isPopArray($args['link'])) || (isset($args['edit_pencil']) && isPopArray($args['edit_pencil'])))
		{
			foreach($result['orgdata'] as $k => $i)
			{
				if(isset($args['edit_pencil']) && isPopArray($args['edit_pencil']))
				{
					$q['edit_pencil'] = '';
					$i = array_merge($q,$i);
					$args['link']['field'] = 'edit_pencil';
					$args['link']['key'] = $args['edit_pencil']['key'];
					
				}
				
				$junk[$k] = $this->makeLinkArray($i, $args['link']['field'], $args['link']['key']);
				
			}
			$result['records'] = $junk;
		}
		
		if($args['return_result_array'])
		{
			return $result;
		}
		
		if($this->page == false)
		{
			if($args['page'] == "" || $args['page'] == false)
			{
				$args['page'] = getView("resultlist.php",'beagleviews');
			}
			else 
			{
				$this->page = $args['page'];
			}
		}
	
		return $this->showTemplate($args['page'],$result);
		
	}
	
	/**
	 * This method get the column headears clean name
	 * @param array $array
	 * @return array $tmp
	 * @author Jason Ball
	 */
	protected function getHeaders($array=array())
	{
		if(is_array($array))
		{
			$tmp = array();

			foreach($array as $k => $i)
			{
				$tmp[$k] = $this->cleanName($k);	
			}
		}
		
		return $tmp;
	}
	
	
	protected function excel($SQL,$views)
	{
		$tmp = array();
		foreach($views as $k => $i)
		{
			$tmp[0][] = $this->cleanName($k,$i);
		}
		$result = $this->db->query($SQL);
		while($row = $result->fetchRow())
		{
			foreach($row as $k => $i)
			{
				if(strpos(strtolower($k),"date")!==false && $i != '' && is_numeric($i))
				{
					$row[$k] = date("m/d/Y",$i);
				}
				if(strpos(strtolower($k),"deadline") !== false && $i != '')
				{
					$row[$k] = date("m/d/Y",$i);
				}
			}
			$tmp[] = $row;	
		}
		return $tmp;
	}
}
?>