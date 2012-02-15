function beagleTimer(obj)
{

	var me = this;
	var message = 'Loading';
	var width = '48px';
	var paddingLeft = '10px';
	var marginLeft = 0;
	var backgroundimg = '/img/icons/ajax-loader.gif';
	this.startWait = startWait;
	this.stopWait = stopWait;
	this.fullUnmask = fullUnmask;
	
	if(isset(obj))
	{
		if(isset(obj.message))
		{
			message = obj.message;
		}
		if(isset(obj.width))
		{
			width = obj.width;
		}
		if(isset(obj.paddingLeft))
		{
			paddingLeft = obj.paddingLeft;
		}
		if(isset(obj.marginLeft))
		{
			marginLeft = obj.marginLeft;
		}
		if(isset(obj.backgroundimg))
		{
			backgroundimg = obj.backgroundimg;
		}
	}
	
	function startWait(id,value)
	{
		if(!isset(id))
		{
			id="container";
		}
		if(!isset(value))
		{
			value=message;
		}
		
		$("#"+id).mask(value);
		$(".loadmask-msg div").css('width',width);
		$(".loadmask-msg div").css('padding-left',paddingLeft);
		$(".loadmask-msg").css('margin-left',marginLeft);
		$(".loadmask-msg div").css('background-image','url('+backgroundimg+')');
	}

	function stopWait(id)
	{
		if(!isset(id))
		{
			id="container";
		}
		
		
		$("#"+id).unmask();
	}
	
	function fullUnmask(main)
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
						fullUnmask(child[a]);
		
					}
					var id = child[a].id;
					if(isset(id) && id != "")
					{
						$("#"+id).unmask();
					}
				}
				
				var mid = main.id;
				if(isset(mid) && mid != "")
				{
					$("#"+mid).unmask();
				}
				
			}
		}
		
		
	}
	
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
}