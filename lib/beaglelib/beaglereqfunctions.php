<?php
/**
 * This function will find the view page and give you the file path or relitive path if you pass a flag.  Returns false if it can't find the file
 * @param string $sheet			file or subsystem/file
 * @param sring $system			primary folder under view filder
 * @param boolean $relitive		is this for a web page (css,js,ext) or you need the full path for PHP
 * @param $clienttest			blank
 */
function getView($sheet,$system="",$relitive=false,$clienttest="")
{
	if(function_exists('getViewOverride'))
	{
		return getViewOverride($sheet,$system,$relitive,$clienttest);
	}
	
	//If you don't pass any extention we will try and default php
	if(strpos($sheet,'.') ===false)
	{
		$sheet = $sheet.".php";
	}
	
	if($relitive == true)
	{
		return "/views/".$system."/".$sheet;
	}
	else 
	{
		return __SYSTEM_ROOT__."/lib/views/".$system."/".$sheet;
	}
}

/**
 * This will do a lot of the relitive stuff for me
 */
function setGlobalVars()
{
	if(is_cli())
	{
		$root = __CLI_ROOT__;
		$getcwd = getcwd();
		$_SERVER['SCRIPT_FILENAME'] = $getcwd."/cli.php";
	}
	else 
	{
		$root = __WEB_ROOT__;
	}
	if(defined('__DOC_ROOT__')) { return false; }
	
	if (preg_match('/^(.*)\/'.$root.'\//', $_SERVER['SCRIPT_FILENAME'], $m)) 
	{
		
		define("__DOC_ROOT__", $m[1]."/".$root);
		define("__SYSTEM_ROOT__",$m[1]);
	}
	elseif (defined('__SYSTEM_ROOT__')) 
	{
		
		define("__DOC_ROOT__", __SYSTEM_ROOT__."/htdocs");
		//define("__SYSTEM_ROOT__",$m[1]);
	}
	elseif(preg_match('/^(.*)\/'.$root.'/', $_SERVER['PHP_SELF'], $m))
	{
		define("__DOC_ROOT__", $m[1]."/".$root);
		define("__SYSTEM_ROOT__",$m[1]);
	}
	else
	{
		error_log("unable to determine DOC/SYSTEM root");
		print "unable to determine DOC/SYSTEM root\n";
		exit;
	}
}

/**
 * I often have to make sure this is an array and it is > 0
 * @param array $array
 * @return boolean
 * @author Jason Ball
 */
function isPopArray(&$array)
{
	if(isset($array))
	{
		if(is_array($array))
		{
			if(count($array)>0)
			{
				return true;
			}
		}
	}
	
	return false;
}

/**
 * Function to allow you to an element from an array without having to do all the checking to keep errors down
 * @param array $array
 * @param element $item
 * @return element or false
 * @author Jason Ball
 */
function getValue(&$array,$item)
{
	if(isset($array))
	{
		if(is_array($array))
		{
			if(is_array($item))
			{
				$tmp = $array;
				foreach($item as $i)
				{
					if(isset($tmp[$i]))
					{
						$tmp = $tmp[$i];
					}
					else 
					{
						return false;
					}
				}
				
				return $tmp;
			}
			else 
			{
				if(isset($array[$item]))
				{
					return $array[$item];
				}
			}
		}
	}
	
	return false;
}

/**
 * Are you in CLI mode, 
 * @return boolean
 */
function is_cli()
{
    return php_sapi_name() === 'cli';
}

/**
 * Function to return a nice clean backtrace of data
 * @author Brad Dutton
 */
function cleanBackTrace()
{
	$return = "\n";
	if(!is_cli())
	{
		$return = "<br/>";
	}
	
	$error = '';
	foreach (debug_backtrace() as $i)
	{
    	if (isset($i['file']) && $i['function'] != 'cleanBackTrace')
    	{
      		$error .= $i['function'].'() at '.$i['file'].' line '.$i['line'] . $return;
    	}
	}

	return $error;
	
}

/**
 * Function to check if a variable is set and is numeric
 * @param unknown_type $val
 * @return boolean
 * @author Jason Ball
 */
function isSetNum(&$val)
{
	if(isset($val))
	{	
		if(is_numeric($val) && $val !== false)
		{
			return true;
		}
	}
	
	return false;
}

/**
  * Nice logging feature to debug stuff with.  The log is at /tmp/beagle.log
  * @param anything $item
  * @return void
  * @author Jason Ball
  * @copyright 2011-07-19
 */
function writeLog($item,$backtrace=false,$location="")
{
	if(defined("__LOG_LOCATION__"))
	{
		$h = fopen(__LOG_LOCATION__."/beagle.log","a");
	}
	else if($location == "")
	{
		$h = fopen("/tmp/beagle.log","a");
	}
	else
	{
		$h = fopen($location."\beagle.log","a");
	}
	
	fwrite($h,"\n".date("Y-m-d G:i:s \n"));
	fwrite($h,print_r($item,true)."\n");
	if($backtrace == true)
	{
		fwrite($h,br2nl(cleanBackTrace()));
	}
	
	fclose($h);
}

/**
 * Print SQL in a nice format for debugging
 * @param string $SQL
 * @return void
 * @author Jason Ball
 */
function printSQL($SQL)
{
	if(!is_cli())
	{
		print(nl2br($SQL));
	}
	else 
	{
		print($SQL);
	}
}

/**
 * for a nice looking print_r in html format
 * @param $array
 * @param $return (print for var)
 * @author Jason Ball
 */
function print_r2($array,$return = false)
{
	$jason = print_r($array,true);
	$jason = str_replace(" ","&nbsp;",$jason);
	if($return == true)
	{
		 return nl2br($jason);	 
	}
	else
	{
		 echo nl2br($jason); 
	}
		
}

/**
 * Used to get core classes quickly
 * @param string $class
 * @return boolean
 * @author Jason Ball
 * @copyright 2011-07-30
 */
function beagleClasses($class)
{
	$rp = __SYSTEM_ROOT__;

	$classname = strToLower($class);

	$root = $rp."/lib/beaglelib/";
	
	
	switch($classname)
	{
		case "beaglebase":
		{
			include_once $root."beaglebase.php";
			return true;
		}
		case "breadcrumbclass":
		{
			include_once $root."breadcrumbclass.php";
			return true;
		}
		case "beagledbclass":
		{
			include_once $root."beagledbclass.php";
			return true;
		}
		case "excel":
		{
			include_once $root."excel.php";
			return true;
		}
		case "beaglelisttools":
		{
			include_once $root."beaglelisttools.php";
			return true;
		
		}
		case "beaglenavigationclass":
		{
			include_once $root."beaglenavigationclass.php";
			return true;
		}
		case "mydb":
		{
			include_once $root."mydb.php";		
			return true;
		}
		case "pgdb":
		{
			include_once $root."pgdb.php";
			return true;
		}
		case "beaglesearchclass":
		{
			include_once $root."beaglesearchclass.php";
			return true;
		}
		case "systemaccessclass":
		{
			include_once $root."systemaccessclass.php";
			return true;
		}
		case "beagleresultclass":
		{
			include_once $root."beagleresultclass.php";
			return true;
		}
		case "beagleresultedithtmlclass":
		{
			include_once $root."beagleresultedithtmlclass.php";
			return true;
		}
		case "beagledbsessions":
		{
			include_once $root."beagledbsessions.php";
			return true;		
		}
		case "beagleerrorbase":
		{
			include_once $root."beagleerrorbase.php";
			return true;		
		}
	}
}

/**
 * Magic function for loading classes
 * @param string $orig_classname
 */
function __autoload($orig_classname)
{ 
	
	if($orig_classname == "PEAR_Error")
	{
		return false;
	}

	$classname = $orig_classname;
	setGlobalVars();

	$rp = __SYSTEM_ROOT__;

	
	//Used to get core classes quickly
	if(beagleClasses($classname))
	{
		return true;
	}
	
	if(function_exists('regClasses'))
	{
		if(regClasses($orig_classname))
		{
			return true;
		}
	}
	
	if(strpos(strtoupper($classname),strtoupper("class"))!==false)
	{
		$root = $rp."/lib/classes";
	}
	elseif(strpos(strtoupper($classname),strtoupper("controller")) != false)
	{
		$root = $rp."/lib/controllers";
	}
	else 
	{
		$root = $rp."/lib/models";
	}
	
	
	$dirs = array();

	$found = findFile($root,$classname.'.php');
	
	if($found == true)
	{
		return $found;
	}
	else 
	{
		$root = "/lib";
		$found = findFile($root,$classname.'.php');
		if($found == true)
		{
			return $found;
		}
	}
	return false;

	/* if we didn't find anything search the include_paths */
	$paths = explode(':', ini_get('include_path'));
	foreach ($paths as $path)
	{
		if (includeIfExists($path.'/'.$orig_classname.'.php'))
		{
			return true;
		}
	}

	error_log("Can't find file for class: $orig_classname");
	print "Can't find file for class: $orig_classname<br>\n";

	return false;
}

/**
 * part of the __autoload magic function
 * @param string $file
 * @return boolean
  */
function includeIfExists($file)
{
	if (is_file($file))
	{
		include_once($file);
		return true;
	}

	return false;
}

/**
 * 
 * create a parent child array from a single array with parent child connectors
 * @param array $inArray		What you have
 * @param array $outArray		array to pass child relationship to
 * @param mixed $parent_id
 * @param mixed $child_id
 * @param mixed $currentParentId
 */
function makeParentChildRelations(&$inArray, &$outArray,$parent_id, $child_id, $currentParentId = 0)
{
	if(!is_array($inArray)) 
	{
		return;
	}

	if(!is_array($outArray)) 
	{
		return;
	}

	foreach($inArray as $key => $tuple) 
	{
		if($tuple[$child_id] == $currentParentId) 
		{
			$tuple['children'] = array();
			makeParentChildRelations($inArray, $tuple['children'], $parent_id,$child_id,$tuple[$parent_id]);
			$outArray[] = $tuple; 
		}
	}
}

/**
 * This function will create a JS object out of an array
 * @param array $array
 * @param array $ignore
 */
function arrayToJSObject($array,$ignore=array())
{
	$tmp = array();
	if(is_array($array))
	{
		foreach($array as $vk => $v)
		{
			if(!ignoreItem($vk,$ignore))
			{
				if(is_array($v))
				{
					$tmp[] = $vk.":{".join(",",arrayToObject($v,$ignore))."}";	
				}
				else
				{
					$tmp[] = $vk.":'".escapeJs($v)."'";
				}
				
			}
		}
	}
	
	return $tmp;
}

function ignoreItem($item,$ignore=array())
{
	foreach($ignore as $k => $i)
	{
		if($item == $i)
		{
			return true;
		}
	}
	return false;
}

/**
 * This Function will pull a date value and format it for you
 * Enter description here ...
 * @param array $array
 * @param string $item
 * @return formatted string
 * @author Jason Ball
 */
function getDateValue($array,$item)
{
	$tmp = getValue($array,$item);
	if($tmp != false and $tmp != 0)
	{
		if(is_numeric($tmp))
		{
			return date("m/d/Y",$tmp);
		}
		else 
		{
			return date("m/d/Y",strtotime($tmp));
		}
	}
	
	return "";
}

/**
 * part of the __autoload magic function
 * @param $path
 * @param $file
 * @return boolean
 * @author Brad Dutton
 */
function findFile($path,$file)
{
	if(is_readable($path))
	{
		$files  = scandir($path);
	}
	else
	{
		return false; // if folder is not readable return false.
	}
	
	if(includeIfExists($path.'/'.$file))
	{
		return true;
	}
	else
	{
		foreach ($files as $orig_file)
		{
			# skip hidden files
			if (is_dir($path.'/'.$orig_file) && substr($orig_file,0,1) != '.') 
			{
				$dirs[] = $orig_file;
			}
			
		}
		if(isset($dirs))
		{
			foreach($dirs as $dir)
			{
				$answer = findFile($path."/".$dir,$file);
				if($answer == true)
				{
					return true;
				}
			}
		}
	}
	
	if ($file != strtolower($file)) 
	{
		return findFile($path,strtolower($file));
	}
	
	return false;
}

/**
 * This function is a short cut for breadcrumbclass::storeBCSession
 * 
 * I got tired of typing
 * 
 * @param string $name
 * @param object $class
 * @return void
 * @author Jason Ball
 * @copyright 08/28/2011
 */
function storePageClass($name,$class)
{
	if($name != "" && is_object($class))
	{
		breadcrumbclass::storeBCSession($name,$class);
	}	
}

/**
 * This function is a short cut for breadcrumbclass::restoreBCSession
 * 
 * I got tired of typing
 * 
 * @param string $name
 * @param object $class
 * @return void
 * @author Jason Ball
 * @copyright 08/28/2011
 */
function restorePageClass($name)
{
	if($name != "")
	{
		return breadcrumbclass::restoreBcSession($name);
	}
	return false;	
}

/**
 * This function will store a class in a session variable so it can be passed from page to page
 * @param string $var		array element name
 * @param object $class		Class Object
 * @author Jason Ball
 */
 function storeClass($var,$class)
{
	
	$_SESSION[$var] = base64_encode(serialize(clone($class)));	
}

/**
 * This function will return a stored object to you in object form or return false if not found
 * @param string $var		name of array element
 * @return mixed (object or false)
 * @author Jason Ball
 */
function restoreClass($var)
{
	if(isset($_SESSION[$var]))
	{
		return unserialize(base64_decode($_SESSION[$var]));
	}
	return false;	
}

/**
 * This function just checks to see if the date pass is a valid date
 * @param string $date
 * @reWturn boolean
 * @author Jason Ball
 * @copyright 2011-08-02
 */
function isValidPopDate(&$date)
{
	if(isset($date))
	{
		if(is_numeric(strtotime($date)))
		{
			return true;
		}
	}

	return false;
}

/**
 * will check an array if args are in place and if not it will add the default you add
 * @param array $in_args
 * @param array $defs
 * @return array 
 */
function defaultArgs($in_args, $defs,$allreq=false) 
{
	if (!is_array($defs)) print 'argDefaults called with non-array defs';

	if (!is_array($in_args))
	{
		writeLog('argDefaults called with non-array args');
		writeLog(br2nl(cleanBackTrace()));
		return $defs;
	}

	if($allreq)
	{
		$req = $defs;	
	}
	
	$out_args = array();

	foreach ($defs as $k => $v)
	{
		if(is_array($in_args))
		{
			if(isPopArray($v) && array_key_exists($k,$in_args))
			{
				$out_args[$k] = defaultArgs($in_args[$k],$v);
			}
			else 
			{
				$out_args[$k] = array_key_exists($k, $in_args) ? $in_args[$k] : $defs[$k];
			}
		}
	}

	if($allreq)
	{
		$errors = array();
		foreach($req as $k => $i)
		{
			if($out_args[$k] === false || $out_args[$k] === null)
			{
				$errors[] = $k." is a required field";
			}	
		}
		if(isPopArray($errors))
		{
			$out_args['errors'] = $errors;
		}
	}
	return $out_args;
}

/**
 * This menthod is used to remove empty elements. $type is optional if you want to validate the data as well
 * @param array $array
 * @param string $type 
 * @return Array
 * @author Jason Ball
 * @copyright 8/16/2011
 */
function removeEmptyElements($array,$type="")
{
	if(isPopArray($array))
	{
		$tmp = array();
		foreach($array as $k => $i)
		{
			if($type == "numeric")
			{
				if(is_numeric($i))
				{
					$tmp[$k] = $i;
				}
			}
			else if($i != "")
			{
				$tmp[$k] = $i;
			}
			
		}
		
		return $tmp;
	}
	
}

/**
 * Quick way to show money format
 * @param float number
 * @param integer round
 * @return string
 * @author Jason Ball
 */
function moneyFormat($number,$round = 2)
{
	
	$num =  money_format('%i', round($number,$round));
	
	if (round($number,$round) == 0)
	{
		$num = money_format('%i',0);
	}
	return $num;	
}

/**
 * This method will convert standard objects into an array
 * 
 * @param object $obj
 * @return array
 */
function objectToArray($obj) 
{
	$arr = array();
		
	if(is_object($obj))
	{
		foreach (get_object_vars($obj) as $k => $v)
		{
			if(is_object($v))
			{
				$tmp = objectToArray($v);
				if(is_array($tmp))
				{
					$arr[$k] = objectToArray($tmp);
				}
				else
				{
					$arr[$k] = $tmp;
				}
			}
			else if(is_array($v))
			{
				$tmp = array();
				foreach($v as $m => $d)
				{
					if(is_object($d))
					{
						$tmp[$m] = objectToArray($d);
					}
					else
					{
						$tmp[$m] = $d;
					}
				}
				$arr[$k] = $tmp;
			}
			else
			{
				$arr[$k] = $v;
			}
		} 
		return $arr;
	}
	else if(is_array($obj))
	{
		$tmp = array();
		foreach($obj as $m => $d)
		{
			if(is_object($d))
			{
				$tmp[$m] = objectToArray($d);
			}
			else
			{
				$tmp[$m] = $d;
			}
		}
		return $tmp;
	}
	else
	{
		return $obj;
	}
		
}

/**
 * recursive function for creating a XML string from an array
 * @param array $array
 * @return xml string
 * @author Jason Ball
 */
function arrayToNode($array)
{
	$xml = "";
		
	foreach($array as $k => $i)
	{
		if(is_array($i))
		{
			if(is_numeric($k))
			{
				$xml .= "<Node>\n";
			}
			else
			{
				$xml .= "<".$k.">\n";
			}
				
			$xml .= arrayToNode($i);
				
			if(is_numeric($k))
			{
				$xml .= "</Node>\n";
			}
			else
			{
				$xml .= "</".$k.">\n";
			}
			
		}
		else
		{
			if(is_numeric($k))
			{
				$xml .= "<Node>";
			}
			else 
			{
				$xml .= "<".$k.">";
			}
			
			$xml .= "<![CDATA[".$i."]]>";
			
			if(is_numeric($k))
			{
				$xml .= "</Node>";
			}
			else 
			{
				$xml .= "</".$k.">";
			}
			
		}
	}
	return $xml;
}

/**
 * This method returns the 3 letter extention of a file
 *
 * @param string $name
 * @return string or null
 * @author Jason Ball
 */
function getFileType($name)
{
	$type = substr($name,strlen($name)-4,4);
	if(strpos($type,".")!==false)
	{
		return substr($type,1,3);
	}
	return false;	
		
}

function br2nl($string)
{
	return preg_replace('#<br\s*?/?>#i', "\n", $string);
}

/**
 * This function is to figure out if the string is and HTML string.
 * Used primarily to make sure a php function is not assumed to be a DB fuction
 * 
 * @param string $string
 * @return Boolean
 * @author Jason Ball
 * @copyright 2012-02-23
 */
function isHTMLString($string)
{
	if(strlen($string) != strlen(strip_tags($string)))
	{
  		return true;
	}
	elseif(strpos($string,'<?') !== false && strpos($string, '?>') !== false)
	{
		return true;
	}
	
	return false;
} 

/**
 * 
 * For use of figuring out what is in an array without printing out the whole thing
 * @param array $array
 * @param boolean $write (print to screen (default) or write to beagle.log)
 * @return boolean
 * @author Jason Ball
 * @copyright 2012-02-26
 */
function printArrayKeys($array,$write=false)
{
	if(isPopArray($array))
	{
		$keys = array_keys($array);
		if(isPopArray($keys))
		{
			if($write == false)
			{	
				print_r2($keys);
				return true;
			}
			else 
			{
				writeLog($keys);
				return true;
			}
		}
	}
	return false;
	
	
}

/**
 * This function is useful for swaping two variables
 * 
 * @param anything $A
 * @param anything $B
 * @return overwrites original var
 * @author Jason Ball (taken from Optimus Pete blog)
 */
function swapVar(&$A, &$B)
{
	list($A,$B) = array($B,$A);
}

/**
 * 
 * Verify that the array has no blank elements and if so remove them
 * @param String $delimiter
 * @param array $array
 * @author Jason Ball
 */
function cleanImplode($delimiter=',', $array)
{
	$tmp = array();
	if(isPopArray($array))
	{
		foreach($array as $k => $i)
		{
			if(trim($i) != "")
			{
				$tmp[$k] = $i;
			}
		}

		if(isPopArray($tmp))
		{
			return implode($delimiter,$tmp);
		}
	}
	
	return false;
	
	
}

function callWeb($url,$post=array(),$get=array())
{
	$defaults = array(
			        CURLOPT_POST => true,
			     //   CURLOPT_HEADER => true,
			     	CURLOPT_URL=>$url,
			        CURLOPT_FOLLOWLOCATION => true,
			        CURLOPT_MAXREDIRS => 5,
			        CURLOPT_RETURNTRANSFER => 1,
			        CURLOPT_TIMEOUT => 120,
			        CURLOPT_ENCODING => "",
			        CURLOPT_COOKIEFILE => '/tmp/cookie.txt', 
			        CURLOPT_SSL_VERIFYPEER => false,
			        CURLOPT_SSL_VERIFYHOST => 2,
			        CURLOPT_COOKIEJAR => '/tmp/cookie.txt',
			        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3",
			        CURLOPT_AUTOREFERER => true,
			        CURLOPT_CONNECTTIMEOUT => 120,
			        CURLOPT_POSTFIELDS => http_build_query($post)
			    ); 

	$ch = curl_init();
	curl_setopt_array($ch, ($defaults)); 
	$page = curl_exec($ch);
					
	$header = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
	return $page;
}