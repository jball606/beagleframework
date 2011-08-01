function beagleResults(obj)
{
	var searchfield = new Array();
	var orderlist = new Array();
	var search = "";
	var resultdiv = "";
	var timerdiv = "container";
		
	this.searchResult = searchResult;
	this.checkThis = checkThis;
	this.allCheck = allCheck;
	this.openLink = openLink;
	
	this.userFunction;
	this.accessFunction;
	
	if(isset(obj))
	{
		if(isset(obj.search))
		{
			search = obj.search;
		}
		if(isset(obj.resultdiv))
		{
			resultdiv = obj.resultdiv;
		}
		else
		{
			alert("Result Div not Defined");
		}
		
		if(isset(obj.timerdiv))
		{
			timerdiv = obj.timerdiv;
		}
	}
	else
	{
		alert("Result Div must be set");
	}
	
	
	function searchResult(first,field,orderdir,limit,specialwhere)
	{
		var param = "id=searchresults"+SearchString(first,field,orderdir,limit,specialwhere)+"&search="+search;

		$.ajax({
			   	url:"/ajax/search_ajax.php",
			   	data:param,
				dataType:'html',
				beforeSend:function() { timer.startWait(timerdiv); },
				success:function(html) { 
											$('#'+resultdiv).html(html);	
											timer.stopWait(); 
										}
			   
			   });
		

	}
	
	function openLink(field,id)
	{
		this.userFunction(field,id);
	}
	
	function search()
	{
		var param = $("#searchcriteria").serialize()+"&id=activityaccountsearch";
		$.ajax({
				url:"/ajax/search_ajax.php",
				data:param,
				dataType:'html',
				success:function(html)
				{
					timer.stopwait();
					$("#searchstuff").css('display','none');
					$("#resultlist").html(html);
					$("#resultlist").css('display','');
				}
		});


	}
	
	function checkThis(value,ck)
	{
		$.ajax({
				url:"/ajax/search_ajax.php",
				data:{id:'singlecheck',value:value,search:search,check:ck},
				beforeSend:function() { },
				success:function(json)
				{
					if(ck == false)
					{
						$("#global_"+search).attr('checked',false);
					}
				}
			
		})
		
	}
	
	function allCheck(ck)
	{
		
	
		$.ajax({
				url:"/ajax/search_ajax.php",
				data:{id:'allcheck',search:search,check:ck},
				beforeSend:function() {},
				success:function(json)
				{
					$("."+search).attr('checked',ck);
				}
		
				
			
		});
		
	}
	
	function SearchSort(field)
	{
		
		if(field != undefined && field.length >0)
		{
			var x = $.inArray(field,orderlist); 
			if(x == -1)
			{ 
				x = orderlist.length;	
				searchfield[x] = 1; 
				orderlist[x] = field;
			}
			else
			{
				if(searchfield[x] == 1)
				{ 
					searchfield[x] = 2; 
				}
				else if(searchfield[x] == '')
				{ 
					searchfield[x] = 1;  
				}
				else
				{
					searchfield[x] = 0;
				}
			}
		}
		var final = '';
			
		var j = 0;
		for(var a =0;a<orderlist.length;a++)
		{
			if(orderlist[a] != "")
			{ 
				final += orderlist[a]+"&orderdir="+searchfield[a]+",";	 
			}
		}
		
		if(final.length >0)
		{ 
			final = final.substr(0,final.length-1); 
		}
			
		return final;
		
	}
	
	function SearchString(first,field,orderdir,limit,specialwhere)
	{
				
		if(!first || first == "")
		{ 
			if(first != 0)
			{
				first = lastfirst;  
			}
			if(first == "")
			{
				first = 0; 
			}
		}
		
		if(!limit || isNaN(limit))
		{
			limit = '';
		}
		
		if(!field || field.length == 0)
		{
			field = ''; 
		}
		
		if(!orderdir || orderdir.length == 0)
		{
			orderdir = 0;
		}
		if(!specialwhere || specialwhere.length == 0)
		{
			specialwhere = "";
		}
		
		lastfirst = first;
		
		var line = "&orderby="+field+"&orderdir="+orderdir+"&excel=N&first="+first+"&limit="+limit+"&specialwhere="+encodeURIComponent(specialwhere);
		
		return line;
	}
}