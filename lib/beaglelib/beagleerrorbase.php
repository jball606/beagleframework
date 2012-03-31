<?php
/**
 * The Beagle Error Base class is so you don't have to write the same error methods over and over again
 * @author Jason Ball
 * @package Beagleframework
 * 
 */
class beagleerrorbase
{
	protected $error = array();

	/**
	 * Store a sting into the error system to return to the user
	 * 
	 * @param string/array $error
	 * @return void
	 * @author Jason Ball
	 */
	protected function storeError($error)
	{
		if(is_array($this->error))
		{
			if(isPopArray($error))
			{
				foreach($error as $i)
				{
					$this->error[] = $i;
				}
			}
			else 
			{
				$this->error[] = $error;
			}
		}
		else 
		{
			$this->error = $error;
		}
		
		if(isPopArray($error))
		{
			foreach($error as $i)
			{
				writeLog(date("Y-m-d G:i:s"));
				writeLog("USER ERROR = ".$i);
			}
		}
		else 
		{	
			writeLog(date("Y-m-d G:i:s"));
			writeLog("USER ERROR = ".$error);
		}
		
		writeLog(br2nl(cleanBackTrace()));
	}
	
	/**
	 * make the pretty UL fail for good user UI
	 * @param $value (string, usually $this->error)
	 * @return html string or false
	 */
	protected function prettyFail($value="")
	{
		if($value == "")
		{
			return false;
		}
		
		$tmp = '<ul class="erroralert">';
		if(isPopArray($value))
		{
			foreach($value as $i)
			{
				$tmp .= '<li>'.$i."</li>";
			}
		}
		else 
		{
			$tmp .= '<li>'.$value.'</li>';
		}
		$tmp .= '</ul>';
		
		return $tmp;
	}
	
	/**
	 * Because I have a lot of store error then right after that return error
	 * 
	 * @param string/array $error
	 * @return string/array/boolean
	 * @author Jason Ball
	 */
	public function storeAndGetError($error="")
	{
		$this->storeError($error);
		return $this->getError($error);	
	}
	
	/**
	 * Simple method to see if an error already exist.
	 * @param void
	 * @return mixed (false or string)
	 */
	public function getError()
	{
		if(isPopArray($this->error) || (!is_array($this->error) && strlen(trim($this->error)) != 0))
		{
			if(!is_array($this->error))
			{
				return $this->error;
			}
			elseif(is_array($this->error) && count($this->error) == 1)
			{
				return $this->error[0];
			}
			else 
			{
				return $this->error;
			}
		}
		
		return false;
	}
}
?>