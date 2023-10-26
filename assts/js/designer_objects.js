(function ( $ ) {
    $.fn.txtPrinter = function( options ) { 
        var optData = $.extend({ name: 'designer_text', text:'Demo', containment:'', required:0, css:{fontfamily:'', fontColor:'', fontSize:90, fontStyle:'', 
										left:100, top:40, imageWidth:500, imageHeight:500 }},options); 
		var cssData = optData.css;
		if($('#'+optData.name,this).length > 0){  
			cssData.left = parseInt($('#'+optData.name,this).css('left'), 10);
			cssData.top =  parseInt($('#'+optData.name,this).css('top'), 10);
			cssData.fontSize =  parseInt($('#'+optData.name+' .ovl-txt',this).css('font-size'), 10); 
			$('#'+optData.name,this).remove(); 
		}  
		var txtObject = $('<div class="gftw pText" id="'+optData.name+'" style="left:'+cssData.left+'px; top:'+cssData.top+'px"></div>').appendTo(this);
		txtObject.append( function() { return $('<span class="close ui-icon ui-icon-trash"></span>',this).bind('click',function(){ $(this).closest('.ui-draggable').remove() }) });
		cssData.imageWidth=txtObject.closest(optData.containment).width();
		cssData.imageHeight=txtObject.closest(optData.containment).height();
		txtObject.append(function() { if(optData.required == 1){ return '<div class="gftw-required">*Required</div>'; } }) 
		.append('<div class="ovl-txt" style="font-family:\''+cssData.fontfamily+'\'; font-size:'+cssData.fontSize+'px; color:'+cssData.fontColor+'; font-Style:'+cssData.fontStyle+'; ">'+optData.text+'</div>')
		.append('<input type="hidden" value=\'{"text":"'+optData.text+'", "required":'+optData.required+', "css":'+JSON.stringify(cssData)+'}\' name="'+optData.name+'"/>') 
		.draggable({ containment:optData.containment, stop: function(event, ui){ cssData.top = ui.position.top; cssData.left = ui.position.left; $('input', this).val('{"text":"'+optData.text+'", "required":'+optData.required+', "css":'+JSON.stringify(cssData)+'}'); }  })  
		.resizable({ aspectRatio: true, 
					resize: function(e,ui) {  
						  cssData.fontSize = (parseFloat($(this).width()) / parseFloat($('.ovl-txt', this)
						  .width())) * parseFloat($('.ovl-txt', this).css('font-size'));
						  $('.ovl-txt', this).css('font-size',(cssData.fontSize-2));
					},
					stop: function(event, ui){ $('input', this).val('{"text":"'+optData.text+'", "required":'+optData.required+', "css":'+JSON.stringify(cssData)+'}'); }
		 }); 
    };
}( jQuery )); 
(function ( $ ) { 
		$.fn.imgPrinter = function( options ) { 
		var optData = $.extend({ name: 'designer_image', url:'', containment:'', attachement:20, css:{width:100, left:100, top:140, imageWidth:500, imageHeight:500}},options); 
		var cssData = optData.css;
		if($('#'+optData.name,this).length > 0){ 
			cssData.left = parseInt($('#'+optData.name,this).css('left'), 10);
			cssData.top =  parseInt($('#'+optData.name,this).css('top'), 10);
			cssData.width =  parseInt($('#'+optData.name,this).css('width'), 10);
			//cssData.height =  parseInt($('#'+optData.name,this).css('height'), 10);
			$('#'+optData.name,this).remove(); 
		}
		var imgObject = $('<div id="'+optData.name+'" class="gftw pImage" style=" left:'+cssData.left+'px; top:'+cssData.top+'px; width:'+cssData.width+'px; height:'+cssData.height+'px"></div>').appendTo(this);
		cssData.imageWidth=imgObject.closest(optData.containment).width();
		cssData.imageHeight=imgObject.closest(optData.containment).height();
		imgObject.append( function() { return $('<span class="close ui-icon ui-icon-trash"></span>',this).bind('click',function(){ $(this).closest('.ui-draggable').remove(); }); })
		.append('<img src="'+optData.url+'"/>') 
		.append('<input type="hidden" value=\'{"attachement":"'+optData.attachement+'", "css":'+JSON.stringify(cssData)+'}\' name="'+optData.name+'"/>') 
		.draggable({ handle: "img", containment:optData.containment,  stop: function(event, ui){ cssData.top = ui.position.top; cssData.left = ui.position.left; $('input', this).val('{"attachement":"'+optData.attachement+'", "css":'+JSON.stringify(cssData)+'}'); } }) 
		.resizable({ aspectRatio: true, 
					resize: function(e,ui) { },
					  stop: function(event, ui){ cssData.width = ui.size.width; cssData.height = ui.size.width; $('input', this).val('{"attachement":"'+optData.attachement+'", "css":'+JSON.stringify(cssData)+'}'); }
		 });  
	}   
}( jQuery ));
