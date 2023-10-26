/*//Designer Text Object
$.widget("gftw.txtPrinter" ,{ 
	_create:function(){
		var optData = this.options;
		var cssData = optData.css; 
		$('#'+optData.name,this.element).remove();  
		this.txtObject = $('<div class="gftw pText" id="'+optData.name+'" style="left:'+cssData.left+'px; top:'+cssData.top+'px"></div>').appendTo(this.element);
		this.txtObject.append( function() { return $('<span class="close ui-icon ui-icon-trash"></span>',this).bind('click',function(){ $(this).closest('.ui-draggable').remove() }) }) 
		.append('<div class="ovl-txt" style="font-family:\''+cssData.fontfamily+'\'; font-size:'+cssData.fontSize+'px; color:'+cssData.fontColor+'; font-Style:'+cssData.fontStyle+'; ">'+optData.text+'</div>')
		.append('<input type="hidden" value=\'{text:"'+optData.text+'", css:'+JSON.stringify(cssData)+'}\' name="'+optData.name+'"/>') 
		.draggable({ containment:optData.containment, stop: function(event, ui){ cssData.top = ui.position.top; cssData.left = ui.position.top; $('input', this).val('{text:"'+optData.text+'", css:'+JSON.stringify(cssData)+'}'); }  })  
		.resizable({ aspectRatio: true, 
					resize: function(e,ui) {  
						  cssData.fontSize = (parseFloat($(this).width()) / parseFloat($('.ovl-txt', this)
						  .width())) * parseFloat($('.ovl-txt', this).css('font-size'));
						  $('.ovl-txt', this).css('font-size',(cssData.fontSize-2));
					},
					stop: function(event, ui){ $('input', this).val('{text:"'+optData.text+'", css:'+JSON.stringify(cssData)+'}'); }
		 });  
	},
	_refresh: function() {
		 alert('Refreshed');
	},
	change: function() {
		alert( "The value has changed!" );
	 }
});
//Designer Image Object
$.widget("gftw.imgPrinter" ,{ 
	_create:function(){  
		var optData = this.options;
		var cssData = optData.css;
		$('#'+optData.name,this.element).remove();
		this.txtObject = $('<div class="gftw pImage" style=" left:'+cssData.left+'px; top:'+cssData.top+'px; width:'+cssData.width+'px; height:'+cssData.height+'px"></div>').appendTo(this.element);
		this.txtObject.append( function() { return $('<span class="close ui-icon ui-icon-trash"></span>',this).bind('click',function(){ $(this).closest('.ui-draggable').remove(); }); })
		.append('<img src="'+optData.url+'"/>') 
		.append('<input type="hidden" value=\'{attachement:"'+optData.attachement+'", css:'+JSON.stringify(cssData)+'}\' name="'+optData.name+'"/>') 
		.draggable({ handle: "img", containment:optData.containment,  stop: function(event, ui){ cssData.top = ui.position.top; cssData.left = ui.position.top; $('input', this).val('{attachement:"'+optData.attachement+'", css:'+JSON.stringify(cssData)+'}'); } }) 
		.resizable({ aspectRatio: true, 
					resize: function(e,ui) {  
						  var font_size = (parseFloat($(this).width()) / parseFloat($('.ovl-txt', this)
						  .width())) * parseFloat($('.ovl-txt', this).css('font-size'));
						  $('.ovl-txt', this).css('font-size',(font_size-2));
					},
					  stop: function(event, ui){ cssData.width = ui.size.width; cssData.height = ui.size.width; $('input', this).val('{attachement:"'+optData.attachement+'", css:'+JSON.stringify(cssData)+'}'); }
		 });  
	}   
});*/
//Designer Text Object
(function ( $ ) {
    $.fn.txtPrinter = function( options ) {
        var optData = $.extend({ name: 'designer_text', text:'Demo', containment:'', required:0, css:{fontfamily:'', fontColor:'', fontSize:22, fontStyle:'', 
										left:100, top:40 }},options); 
		var cssData = optData.css; 
		$('#'+optData.name,this).remove();   
		var txtObject = $('<div class="gftw pText" id="'+optData.name+'" style="left:'+cssData.left+'px; top:'+cssData.top+'px"></div>').appendTo(this);
		txtObject.append( function() { return $('<span class="close ui-icon ui-icon-trash"></span>',this).bind('click',function(){ $(this).closest('.ui-draggable').remove() }) }) 
		.append('<div class="ovl-txt" style="font-family:\''+cssData.fontfamily+'\'; font-size:'+cssData.fontSize+'px; color:'+cssData.fontColor+'; font-Style:'+cssData.fontStyle+'; ">'+optData.text+'</div>')
		.append('<input type="hidden" value=\'{text:"'+optData.text+'", css:'+JSON.stringify(cssData)+'}\' name="'+optData.name+'"/>') 
		.draggable({ containment:optData.containment, stop: function(event, ui){ cssData.top = ui.position.top; cssData.left = ui.position.top; $('input', this).val('{text:"'+optData.text+'", css:'+JSON.stringify(cssData)+'}'); }  })  
		.resizable({ aspectRatio: true, 
					resize: function(e,ui) {  
						  cssData.fontSize = (parseFloat($(this).width()) / parseFloat($('.ovl-txt', this)
						  .width())) * parseFloat($('.ovl-txt', this).css('font-size'));
						  $('.ovl-txt', this).css('font-size',(cssData.fontSize-2));
					},
					stop: function(event, ui){ $('input', this).val('{text:"'+optData.text+'", css:'+JSON.stringify(cssData)+'}'); }
		 }); 
    };
}( jQuery ));

//Designer Image Object
(function ( $ ) { 
		$.fn.imgPrinter = function( options ) {
		var optData = $.extend({ name: 'designer_image', url:'', containment:'', attachement:20, css:{width:100, height:100, left:100, top:140}},options); 
		var cssData = optData.css;
		$('#'+optData.name,this).remove();
		var imgObject = $('<div class="gftw pImage" style=" left:'+cssData.left+'px; top:'+cssData.top+'px; width:'+cssData.width+'px; height:'+cssData.height+'px"></div>').appendTo(this);
		imgObject.append( function() { return $('<span class="close ui-icon ui-icon-trash"></span>',this).bind('click',function(){ $(this).closest('.ui-draggable').remove(); }); })
		.append('<img src="'+optData.url+'"/>') 
		.append('<input type="hidden" value=\'{attachement:"'+optData.attachement+'", css:'+JSON.stringify(cssData)+'}\' name="'+optData.name+'"/>') 
		.draggable({ handle: "img", containment:optData.containment,  stop: function(event, ui){ cssData.top = ui.position.top; cssData.left = ui.position.top; $('input', this).val('{attachement:"'+optData.attachement+'", css:'+JSON.stringify(cssData)+'}'); } }) 
		.resizable({ aspectRatio: true, 
					resize: function(e,ui) {  
						  var font_size = (parseFloat($(this).width()) / parseFloat($('.ovl-txt', this)
						  .width())) * parseFloat($('.ovl-txt', this).css('font-size'));
						  $('.ovl-txt', this).css('font-size',(font_size-2));
					},
					  stop: function(event, ui){ cssData.width = ui.size.width; cssData.height = ui.size.width; $('input', this).val('{attachement:"'+optData.attachement+'", css:'+JSON.stringify(cssData)+'}'); }
		 });  
	}   
}( jQuery ));
