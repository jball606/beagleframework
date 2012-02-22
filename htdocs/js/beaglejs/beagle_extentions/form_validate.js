/*
new jquery plugin for form validate
author Jason Ball
2/14/2012


*/
var formValidateSettingParams = {text:'Required Field',red:'#FF0000',black:'#000000',jump:true,alert:'alert'};

function fvdebug(msg)
{
	alert(msg);
}
jQuery.formValidateSettings = function(s)
{
	formValidateSettingParams = jQuery.extend(formValidateSettingParams,s);
	
}



jQuery.formValidate = function(s)
{	
	
	var red = formValidateSettingParams.red;
	var black = formValidateSettingParams.black;
	var jump = formValidateSettingParams.jump;
	var text = formValidateSettingParams.text; //'Required Field';
	var alert = formValidateSettingParams.alert;
	

	return Validate(s);
	
	
	function Validate(s)
	{
		removeAllDynamicErrors();
		
		s = jQuery.extend({}, s, {});
		
		if(!s.list)
		{
			fvdebug("No Valid List");
			return false;
		}
		
		
		var div = s.list.split(";");
		for(var a =0;a<div.length;a++)
		{
			if(document.getElementById(div[a]) == null)
			{
				fvdebug(div[a]+" "+a+" does not exist!"); 
				return false;
			}
			
						
			var test = document.getElementById(div[a]);
			if(test.tagName == 'DIV')
			{
				var list = $("#"+div[a]).html();
				var field = list.split(";");
			}
			else
			{
				var field = Array();
				field[0] = div[a];
			}
			for(var b = 0;b<field.length;b++)
			{
				var tfield = $.trim(field[b].substr(field[b].indexOf(".")+1,field[b].length));
								
				$("#"+tfield+"_title").removeClass(alert);
				
				var obj = getObj(tfield);

			 
				require(s.list);
				if($.trim(obj.value) == '' || obj.value == '' || obj.value == text)
				{		
					return false;
				}	 

			}
		}
		return true;
		
	};
	
	function getObj(obj, form, lit)
	{
		if(form != "" && form != undefined)
		{ 
			if(lit != undefined)
			{
				return "document.forms['"+form+"']."+obj;  
			}
			else
			{
				return eval('document.forms["'+form+'"].'+obj); 
			}
		}
		else
		{
			if(lit != undefined)
			{
				return "document.getElementById('"+obj+"')"; 
			}
			else
			{
				return document.getElementById(obj); 
			}
		}
			
	}
	

	
	
	function require(divs,form)
	{
		var div = divs.split(";");
		for(var a =0;a<div.length;a++)
		{
			if(document.getElementById(div[a]) == null)
			{
				return false; 
			}
						
			var test = document.getElementById(div[a]);
			if(test.tagName == 'DIV')
			{
				var list = document.getElementById(div[a]).innerHTML;
				var field = list.split(";");
			}
			else
			{
				var field = Array();
				field[0] = div[a];
			}
						
			for(var b=0;b<field.length;b++)
			{
				var tfield = $.trim(field[b].substr(field[b].indexOf(".")+1,field[b].length));
								
								
				var obj = getObj(tfield,form);
								
				if($.trim(obj.value) === "" || obj.value == text || obj.value == '&#160;' || obj.value == " ")
				{ 
					$("#"+obj.id).customError(text);
					if(document.getElementById(tfield+"_title"))
					{
						$("#"+tfield+"_title").addClass(alert);
					}
				}
				else
				{ 
					$("#"+tfield+"_title").removeClass(alert);
				}
			}
		}
		
		if(document.getElementById('infoerror'))
		{
			document.getElementById('infoerror').style.display = ''; 
		}
				
		if(jump == true)
		{
			window.scrollTo(0,0);	
		}
				
	}
	
};


$.fn.customError = function(message)
{
	var elm = $(this);
	var elm_id = elm.attr('id');
		
	var bob = elm.parent();
	if(bob.get(0).tagName == "TD")
	{
		var tr = bob.parent();
			
		tr.find('td').each(function() {
			$(this).attr('valign','top');
		});
	}
		
	if(elm.next().attr('class') == 'ui-datepicker-trigger')
	{
		var tmp = elm.next();
		elm = tmp;
		
	}
		
	$("#"+elm_id+"_dynamic_error").remove();
		
	var list = Array();
		
	if($.isArray(message))
	{
		var x =0;
		for(var a = 0;a<message.length;a++)
		{
			if(message[a] != "")
			{
				list[x] = message[a];
				x++;
			}
		}
	}
	else
	{ 
		list[0] = message;
	}
		
	var msg = '<ul id="'+elm.attr('id')+'_dynamic_error" class="dynamic_error alert" >';
	for(var a = 0; a<list.length;a++)
	{
		msg += '<li>'+list[a]+'</li>';
	}
	msg += '</ul>';
		
	elm.after(msg);
		
}

function removeAllDynamicErrors()
{
	$(".dynamic_error").remove();
		
}