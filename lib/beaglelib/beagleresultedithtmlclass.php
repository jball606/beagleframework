<?php
/**
 * This Class is used to create form elements in a result list so you may edit items directly in the result set.  
 * This manipulates the html not the data it self, you have to pass variables to have the html be created right
 * @author Jason Ball
 * @version 1.0
 * @copyright 08/21/2011
 *
 */
class beagleResultEditHtmlClass extends beaglebase
{
	/**
	 * This array is for quick reference to what HTML type we want, 
	 * @var array $htmltypes
	 */
	private $htmltypes = array('Text','TextArea','CheckBox','Select','Radios');
	
	/**
	 * This array is for the settings neede for the generic system
	 * keys:
	 * 	table = table the value goes into
	 * 	field = field the value goes into
	 * 	htmltype = What html type is the field supose to be, use either the key or name from $this->htmltypes	
	 *	listvalues = array values of a checkbox, select or radio if needed [OPTIONAL]
	 *	js = JS code if you want array [js action][code] if you put <?=$id;?> then the id will be passed [OPTIONAL]
	 *	size = Size of text box [OPTIONAL]
	 * @var unknown_type
	 */
	private $settings = array();

	public function __construct($in_args)
	{
		$args = defaultArgs($in_args, array('
											table'=>false,
											'field'=>false,
											'htmltype'=>FALSE,
											'listvalues'=>array(),
											'js'=>array(),
											'size'=>false,
											));
		
		$this->settings = $args;
	}
	
	public function showFormEdlement($id,$value="")
	{
		if(isPopArray($this->settings))
		{
			$need =  array('table,field,htmltype');
			
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
			}
			else 
			{
				$this->error = "Invalid ID";
			}
		}

		return $this->prettyFail($this->error);
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
		$text = '<input type="text" name="resultedit['.$table.']['.$field.']['.$id.']" id="'.$table."_".$field."_".$id.'" value="'.$value.'" ';
		
		if($size != false)
		{
			$text .= 'size = "'.$size.'"';
		}

		$text .= '/>';
		
		return $text;
	}
}
?>