<?php

function getView($sheet,$system="",$relitive=false,$clienttest="")
{
	if(function_exists('getViewOverride'))
	{
		return getViewOverride($sheet,$system,$relitive,$clienttest);
	}
	
	if($relitive == true)
	{
		return "/views/".$system."/".$sheet;
	}
	else 
	{
		return __DOC_ROOT__."/views/".$system."/".$sheet;
	}
}

/**
 * This will do a lot of the relitive stuff for me
 * Enter description here ...
 * @param unknown_type $webroot
 */
function setGlobalVars()
{
	if(defined('__DOC_ROOT__')) { return false; }
	
	if (preg_match('/^(.*)\/'.__WEB_ROOT__.'\//', $_SERVER['SCRIPT_FILENAME'], $m)) 
	{
		define("__DOC_ROOT__", $m[1]."/".__WEB_ROOT__);
		define("__SYSTEM_ROOT__",$m[1]);
	}
	elseif(preg_match('/^(.*)\/bin/', $_SERVER['PHP_SELF'], $m))
	{
		define("__DOC_ROOT__", $m[1]."/".__WEB_ROOT__);
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
 * @return true/false
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

function cleanBackTrace()
{
	$error = '';
	foreach (debug_backtrace() as $i)
	{
    	if (isset($i['file']) && $i['function'] != 'cleanBackTrace')
    	{
      		$error .= $i['function'].'() at '.$i['file'].' line '.$i['line'] . "\n";
    	}
	}

	return $error;
	
}

/**
 * Function to check if a variable is set and is numeric
 * @param unknown_type $val
 * @return boolian
 * @author Jason Ball
 */
function isSetNum(&$val)
{
	if(isset($val))
	{	
		if(is_numeric($val))
		{
			return true;
		}
	}
	
	return false;
}

/**
 * Nice logging feature to debug stuff with
  * @param anything $item
  * @return void
  * @author Jason Ball
  * @copyright 2011-07-19
 */
function writeLog($item)
{
	$h = fopen("/tmp/beagle.log","a");
	fwrite($h,print_r($item,true));
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
	print(nl2br($SQL));
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
 * Magic function for loading classes
 * @param string $orig_classname
 */
function __autoload($orig_classname)
{ 
	
	if($orig_classname == "PEAR_Error")
	{
		return false;
	}

	setGlobalVars();

	$rp = __SYSTEM_ROOT__;

	$classname = strtolower($orig_classname);

	if(function_exists('regClasses'))
	{
		if(regClasses($orig_classname))
		{
			return true;
		}
	}
	
	if(strpos($classname,"class")!==false)
	{
		$root = $rp."/lib/classes";
	}
	elseif(strpos($classname,"controller") != false)
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
 * @return boolian
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
 * part of the __autoload magic function
 * @param $path
 * @param $file
 * @return boolian
 * @author Brad Dutton
 */
function findFile($path,$file)
{
	$files  = scandir($path);
	
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
	return false;
}

function storeClass($var,$class)
{
	
	$_SESSION[$var] = base64_encode(serialize(clone($class)));	
}

function restoreClass($var)
{
	if(isset($_SESSION[$var]))
	{
		return unserialize(base64_decode($_SESSION[$var]));
	}
	return false;	
}

/**
 * will check an array if args are in place and if not it will add the default you add
 * @param $in_args
 * @param $defs
 * @author Brad Dutton
 */
function defaultArgs($in_args, $defs) 
{
	if (!is_array($in_args)) print 'argDefaults called with non-array args';
	if (!is_array($defs)) print 'argDefaults called with non-array defs';

	$out_args = array();

	foreach ($defs as $k => $v)
	{
		if(is_array($in_args))
		{
			$out_args[$k] = array_key_exists($k, $in_args) ? $in_args[$k] : $defs[$k];
		}
	}

	return $out_args;
}