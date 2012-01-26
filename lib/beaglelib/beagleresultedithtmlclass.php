<?php
/**
 * This Class is used to create form elements in a result list so you may edit items directly in the result set.  
 * This manipulates the html not the data it self, you have to pass variables to have the html be created right
 * @author Jason Ball
 * @version 1.0
 * @copyright 08/21/2011
 \* @package Beagleframework
 * 
 *
 *
 */
class beagleResultEditHtmlClass extends beaglebase
{
	/**
	 * This array is for quick reference to what HTML type we want, 
	 * @var array $htmltypes
	 */
	private $htmltypes = array(1=>'Text',2=>'TextArea',3=>'CheckBox',4=>'Select',5=>'Radio');
	
	/**
	 * This array is for the settings neede for the generic system
	 * <pre>keys:
	 * 	table = table the value goes into
	 * 	field = field the value goes into
	 * 	htmltype = What html type is the field supose to be, use either the key or name from $this->htmltypes	
	 *	listvalues = array values of a checkbox, select or radio if needed [OPTIONAL]
	 *	js = JS code if you want array [js action][code] if you put <?=$id;?> then the id will be passed [OPTIONAL]
	 *	size = Size of text box [OPTIONAL]
	 *  multiple = boolean for if you want your select item to be multiple or not [OPTIONAL]
	 *  check = value to check the box too [OPTIONAL]
	 *  </pre>
	 * @var array
	 */
	private $settings = array();

	public function __construct($in_args)
	{
		$args = defaultArgs($in_args, array('table'=>false,
											'field'=>false,
											'htmltype'=>FALSE,
											'listvalues'=>array(),
											'js'=>array(),
											'size'=>false,
											'multiple'=>false,
											'check'=>false,
											
											));
		$this->settings = $args;
	}
	
	/**
	 * Method to get HTML ID
	 * 
	 * @param void
	 * @return integer
	 * @author Jason Ball
	 * @copyright 08/28/2011
	 */
	public function getHtmlType()
	{
		return $this->settings['htmltype'];
	}
	
	public function showFormElement($id,$value="")
	{
		if(isPopArray($this->settings))
		{
			$need =  array('table','field','htmltype');
			
			foreach($need as $i)
			{
				if(!isset($this->settings[$i]) || $this->settings[$i] == false)
				{
					return $this->prettyFail('Class is missing '.$i." in settings");
				}
			}
			
			
			if(!empty($id) && $id !==false)
			{
				$set = $this->settings['htmltype'];
				$table = $this->settings['table'];
				$field = $this->settings['field'];
				
				
				if($set == 1 || strtolower($set) == "text")
				{
					$size = $this->settings['size'];
					return $this->getTextField($table,$field,$id,$value,$size);
				}
				if($set == 2 || strtolower($set) == "textarea")
				{
					return $this->getTextArea($table,$field,$id,$value);
				}
				if($set == 3 || strtolower($set) == "checkbox")
				{
					return $this->getCheckBox($table, $field, $id,$value);
				}
				if($set == 4 || strtolower($set) == "select")
				{
					return $this->getSelectField($table, $field, $id,$value,$this->settings['multiple'],$this->settings['size']);	
					
				}
			}
			else 
			{
				$this->storeError("Invalid ID");
			}
		}

		return $this->prettyFail($this->error);
	}
	
	/**
	 * This method will return a textarea field for you
	 * @param string $table
	 * @param string $field
	 * @param mixed (string/integer) $id
	 * @param mixed $value
	 * @return string
	 * @author Jason Ball
	 * @copyright 08/21/2011
	 */
	private function getCheckBox($table,$field,$id,$value="")
	{
		$string = '<input type="checkbox" name="resultedit['.$table.']['.$id.']['.$field.']" id="'.$table.'_'.$id.'_'.$field.'"';
		
		if(isset($this->settings['check']))
		{
			$ck = $this->settings['check'];
			$string .= ' value="'.$ck.'" ';
			if($value == $ck)
			{
				$string .= ' CHECKED ';
			}
		}
		$string .= "/>";
		return $string;
	}
	
	/**
	 * This method will return a textarea field for you
	 * @param string $table
	 * @param string $field
	 * @param mixed (string/integer) $id
	 * @param mixed $value
	 * @return string
	 * @author Jason Ball
	 * @copyright 08/21/2011
	 */
	private function getTextArea($table,$field,$id,$value="")
	{
		$ta = '<textarea name="resultedit['.$table.']['.$id.']['.$field.']" id="'.$table.'_'.$id.'_'.$field.'">'.$value."</textarea>\n";
		return $ta;
	}
	
	/**
	 * This method will return a select option field for you
	 * @param string $table
	 * @param string $field
	 * @param mixed (string/integer) $id
	 * @param mixed $value
	 * @param boolean $multiple
	 * @param integer $size
	 * @return string
	 * @author Jason Ball
	 * @copyright 08/21/2011
	 */
	private function getSelectField($table,$field,$id,$value="",$multiple=false,$size=false)
	{
		$l = new beagleListTools();
		
		$select = '<select name="resultedit['.$table.']['.$id.']['.$field.']';
		if($multiple)
		{
			$select .= '[] multiple ';
		}
		if($size)
		{
			$select .= ' size="'.$size.'" ';
		}
		
		$select .= ' id="'.$table.'_'.$id.'_'.$field.'" >';
		
		$select .= $l->SelectedGenArray('id', 'value', $this->settings['listvalues'],$value);
		
		
		$select .= "</select>\n";
		return $select;
	}
	
	/**
	 * This method will return a text area with the preset criteria
	 * @param string $table
	 * @param string $field
	 * @param mixed (string/integer) $id
	 * @param mixed $value
	 * @param integer $size
	 * @return html string $textbox
	 * @author Jason Ball
	 * @copyright 08/21/2011
	 * 
	 */
	private function getTextField($table,$field,$id,$value="",$size=false)
	{
		$text = '<input type="text" name="resultedit['.$table.']['.$id.']['.$field.']" id="'.$table."_".$id."_".$field.'" value="'.$value.'" ';
		
		if($size != false)
		{
			$text .= 'size = "'.$size.'"';
		}

		$text .= '/>';
		
		return $text;
	}
}
?>