
document.observe("dom:loaded", function() {
	qtyswitcher.init();
	
})

qtyswitcher={
	groupedInit: function(quantity){
           
		$$('.qty').each(function(e){
			qty=e.value;
			name=e.name;
			html ="<div class='qtyswitcher-qty'>";
				html+=" <span id='qtyswitcher-qty-box'><label for='qty'>"+quantity+"</label></span>";
				
				html+="<input type='button' id='qtyswitcher-oneless' class='qtyswitcher.btn' onclick='qtyswitcher.removeOne(\""+name+"\",\""+name+"-clone-qty\")'/>";
				html+="<input type='text' class='input-text clone-qty' id='"+name+"-clone-qty' maxlength='12' value='"+qty+"' disabled='true' />";
				html+="<input type='hidden'  name='"+name+"' id='"+name+"' value='"+qty+"'/>";
				html+="<input type='button' id='qtyswitcher-onemore' class='qtyswitcher.btn' onclick='qtyswitcher.addOne(\""+name+"\",\""+name+"-clone-qty\");'/>";
				//html+="<input type='image' id='qtyswitcher-form-btn-products' src='' >"
			html+="</div>"
			e.replace(html);
			
		})
		
	},
	init:function(){
		
	},
	addOne: function(id,idClone){
		count=Math.round($(id).value)+1;
		$(id).writeAttribute('value',count);
		$(idClone).writeAttribute('value',count);		if(count<1) $('addToCartButton').disabled=true;		else $('addToCartButton').disabled=false;
	},
	removeOne: function(id,idClone){
		count=Math.round($(id).value)-1;
		if(count<1)count=1;
		$(id).writeAttribute('value',count);
		$(idClone).writeAttribute('value',count);		if(count<1) $('addToCartButton').disabled=true;		else $('addToCartButton').disabled=false;
	}
	
};