function isset(obj)
{
	if(obj != undefined)
	{
		return true;
	}
	else
	{
		return false;
	}
		
}

function addcal(form,format)
{
	if(format == undefined || format == "")
	{
		format = "mm/dd/yy";
	}
	$(function() 
		{ 
			$("#"+form).datepicker({dateFormat: format, constrainInput:false, showOn: 'button', buttonImage: '/img/icons/calendar.png', buttonImageOnly: true, changeMonth: true, changeYear: true});
		}); 
	
}

function emailCheck(email)
{
	if(email == undefined || email == "")
	{
		return true;		
	}
	var reg = new RegExp(/^([A-Za-z0-9_\-.!#$%^&*+//=?'{|}~]*@[A-Za-z0-9_\-]([A-Za-z0-9_\-]*[A-Za-z0-9_\-])?(\.[A-Za-z0-9_\-]([A-Za-z0-9_\-]*[A-Za-z0-9_\-])?)+)/);
	var awn = reg.test(email);

	if(awn == true)
	{
		var tp = email.split("@");
		if(tp.length > 2)
		{
			return false;
		}
		
	}
	return awn;
	
}

function obj_merge(ob1,ob2)
{
	if(isset(ob1) && isset(ob2))
	{
		var no = {};
		$.extend(no,ob1,ob2);
		return no;
	}
	return false;
}

function addScript(file)
{ 
	document.write('<script language = "javascript" type="text/javascript" src = "'+file+'"></script>'+"\n"); 
}

function serializeRow(parent)
{
	var par = parent;
	var slist="";
	var array = Array();
	var tmp = "";
	for(var a=0;a<par.childNodes.length;a++)
	{
		if(par.childNodes[a].childNodes.length > 0)
		{
			
			if(par.childNodes[a].type == undefined || !par.childNodes[a].type == "select-one")
			{
				array[array.length] = serializeRow(par.childNodes[a]);
				
				
			}
			else
			{
				if(par.childNodes[a].id != "")
				{
					array[array.length] = $('#'+par.childNodes[a].id).serialize();
				}
			}
		}
		else
		{
			tmp = "";
			
			switch(par.childNodes[a].type)
			{
				case "text":
				{
					tmp = $("#"+par.childNodes[a].id).serialize();
					if(tmp != "")
					{
						array[array.length] = tmp;
						tmp = "";
					}
					break;
				}
				case "hidden":
				{
					tmp = $("#"+par.childNodes[a].id).serialize();
					if(tmp != "")
					{
						array[array.length] = tmp;
						tmp = "";
					}
					break;
					
				}
				case "checkbox":
				{
					if(par.childNodes[a].checked == true)
					{
						tmp = $("#"+par.childNodes[a].id).serialize();
						
						if(tmp != "" && tmp.indexOf('=')>-1)
						{
							array[array.length] = tmp;
							tmp = "";
						}
					}
					break;
				}
				default:
				{
					
				}
			}
		}
	}
	
	if(array.length >0)
	{
		slist = array[0];
		for(a=1;a<array.length;a++)
		{
			slist = slist+"&"+array[a];
		}
	}
	
	return slist;	
}

function trim(junk) 
{ 
	if(junk != undefined && junk != "")
	{
		junk = junk.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
		junk = junk.replace(/^\u00A0/,'').replace(/\u00A0/,'');
		return junk;
	}
	else
	{
		return "";
	}
}

function clearError(id,subid)
{

	if(id != undefined && id != "")
	{
		removeChildren(document.getElementById(id));
		$("#"+id).css('display','none');
	}
	
	if(subid != undefined && subid != "")
	{
		$("#"+subid).css('display','none');
	}
	
}

function showError(elist,id,subid)
{
	if(elist != "")
	{
		var list = Array();
		
		if($.isArray(elist))
		{
			var x =0;
			for(var a = 0;a<elist.length;a++)
			{
				if(elist[a] != "")
				{
					list[x] = elist[a];
					x++;
				}
			}
		}
		else
		{
			list[0] = elist;
		}
		
		if(id != undefined && id != "")
		{
			if(document.getElementById(id))
			{	
				var obj = document.getElementById(id);
				for(var a =0;a<list.length;a++)
				{
					var er = document.createElement('li');
					er.innerHTML = list[a];
					obj.appendChild(er);
				}
				$("#"+id).css('display','block');
			}
		}
		
		
		if(subid != undefined && subid != "")
		{
			$("#"+subid).css('display','table-row');
		}
	}
}

function clearBreadCrumbData(url)
{
	url = url.replace("&pcrumb=1","");
	url = url.replace("?pcrumb=1","");

	if(url.indexOf("&") != -1 && url.indexOf("?") == -1)
	{
		url = url.replace("&","?");
	}
	
	return url;
}

function onEnter(e,func,actionkey)
{
	if(actionkey == undefined)
	{
		actionkey = 13; 
	}
	
	var key;
	if(e.which == undefined)
	{ 
		key = window.event.keyCode; 
	}
	else
	{
		key = e.which; 
	}
	
	if(key==actionkey)
	{
		eval(func+"();"); 
	}
}

function removeChildren(main,wmain)
{
	if(isset(main))
	{
		var child = main.childNodes;
		if(isset(child))
		{
			for(var a = child.length-1;a>= 0; a--)
			{
				if(child[a].childNodes.length>0)
				{
					removeChildren(child[a]);
	
				}
				main.removeChild(child[a]); 
				
			}
			if(wmain == true)
			{
				var parent = main.parentNode;
				parent.removeChild(main);
			}
		}
	}
}