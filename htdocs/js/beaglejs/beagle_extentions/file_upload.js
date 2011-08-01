/* this is a very hacked version of  ajaxfileupload.  I need to pass the field by name and not element. 
 * Original at  http://www.phpletter.com/Our-Projects/AjaxFileUpload/
 * Jason Ball
 * 1/2/2011
 */

jQuery.extend({
	//Same as original, I needed it Iframe
	createUploadIframe: function(id, uri)
	{
		if(!document.getElementById(frameId))
		{
			//create frame
            var frameId = 'jUploadFrame' + id;
			var nuri;
            if(typeof uri== 'boolean'){
               nuri = 'javascript:false';
            }
            else if(typeof uri== 'string'){
                nuri = uri;
            }
            
            var frame = $('<iframe />', 
                    	{
							name: frameId,
							id:frameId,
							src:nuri
			            });

			frame.css('position','absolute');
			frame.css('display','none');
			frame.appendTo('body');
		}
		else
		{
			frame = $("#"+frameId);
		}
		
		return frame;
    },
    createUploadForm: function(id, fileFormClass,url)
	{
		//create form	
		var formId = 'jUploadForm' + id;
		var fileId = 'jUploadFile' + id;
		
		var form = $('<form  action="'+url+'" method="POST" target="jUploadFrame'+id+'" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');	
		form.css('display','none');
		form.appendTo('body')
		var elm = $('.'+fileFormClass);
		
		elm.each(function() 
		{ 
			
			var cloned = $(this).clone();
			$(this).attr('id',$(this).attr('id')+1);
			$(this).before(cloned);
			$(this).appendTo(form);

		});
		
	
		return form;
    },
	
	ajaxUpload:function(s)
	{
		s = jQuery.extend({}, jQuery.ajaxSettings, s);
		
		 
		var id = new Date().getTime();
		
		var io = jQuery.createUploadIframe(id, s.secureuri);
		var form = jQuery.createUploadForm(id, s.fileFormClass,s.url);
		
		
		var frameId = 'jUploadFrame' + id;
		var formId = 'jUploadForm' + id;		
		
		io.load(function() 
			{
				//Cleanup
					
					//No loader of death
					io.html('');
					
					form.remove();
					
					
					if(s.success)
					{
						var su = s.success;
						su();
					}
					
			}); 
		form.submit(); 

		
		
		 
	},
	
})