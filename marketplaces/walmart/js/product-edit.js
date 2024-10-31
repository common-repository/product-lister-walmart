var currentRequest = null;
jQuery(document).ready(function(){

	renderMarketplaceAttributesSectionHTML( jQuery('select[name^=_umb_walmart_category]'), jQuery('select[name^=_umb_walmart_category]').val() , ced_umb_walmart_edit_product_script_AJAX.product_id , '' );
		
	jQuery(document.body).on( 'change', 'select[name^=_umb_walmart_category]', function() {
		renderMarketplaceAttributesSectionHTML( jQuery(this), jQuery(this).val() , ced_umb_walmart_edit_product_script_AJAX.product_id , '' );
	});

	jQuery(document.body).on( 'click', 'div.woocommerce_variation h3', function() {
		var indexToUse = jQuery(this).find('input:hidden[name^=variable_post_id]').attr('name');
		indexToUse = indexToUse.split("]")[0].split("[")[1]; 
		var product_id = jQuery(this).find('input:hidden[name^=variable_post_id]').val();
		var categoryID = jQuery(this).next().find('select[name^=_umb_walmart_category]').val();
		var thisRef = jQuery(this).next().find('select[name^=_umb_walmart_category]');
		renderMarketplaceAttributesSectionHTML( thisRef, categoryID , product_id , indexToUse );
	});
	//FUNCTION TO RENDER WALMART ATTRIBUTES SECTION
	function renderMarketplaceAttributesSectionHTML( thisRef, categoryID, productID, indexToUse ) {
		jQuery.ajax({
			url : ced_umb_walmart_edit_product_script_AJAX.ajax_url,
			type : 'post',
			data : {
				action : 'fetch_walmart_attribute_for_selected_category',
				categoryID : categoryID,
				productID : productID,
				indexToUse : indexToUse
			},
			success : function( response ) 
			{
				if( jQuery(thisRef).parent().next().hasClass('ced_umb_walmart_attribute_section') ) {
					jQuery(thisRef).parent().next().remove();
				}
				jQuery(thisRef).parent().after(response);
				jQuery( document.body ).trigger( 'init_tooltips' );
			}
		});
	}
	jQuery(document).on('click','#ced_umb_walmart_taxcode',function(){
		jQuery(".ced_umb_taxcode_overlay_wrapper").show();
	});
	jQuery(document).on('click','.ced_umb_cancel',function(){
		jQuery(".ced_umb_taxcode_overlay_wrapper").hide();
	});
	jQuery(document).on('click','.ced_umb_taxcode_overlay_wrapper table tbody tr',function(){
		var taxocde = jQuery(this).attr('data-taxcode');
		jQuery(this).parents(".ced_umb_taxcode_overlay_wrapper").next().find("input[name ^= 'ToolsAndHardware_productTaxCode']").val(taxocde);
		jQuery(".ced_umb_taxcode_overlay_wrapper").hide();
	});
jQuery(document.body).on('keyup', '#ced_umb_walmart_taxcode_search', function(e){
	if(e.keyCode == 37 && e.keyCode == 38 && e.keyCode == 39 && e.keyCode == 40){
		return;
	}
	
	var stringTobesearched = jQuery(this).val();
	if(stringTobesearched.length <= 1)
	{
		return;
	}
	
	var data = {
					'action' : 'ced_umb_taxcode_search',
					'stringTobesearched' : stringTobesearched
				}
	 
	currentRequest = jQuery.ajax({
		url : ced_umb_walmart_edit_product_script_AJAX.ajax_url,
		type : 'post',
		data : data,
		beforeSend : function()    
		{           
			if(currentRequest != null) 
			{
	            currentRequest.abort();
	        }
	    },
		success : function( response ) 
		{
			jQuery('#ced_umb_walmart_table tbody').html(response);
		}
	});
})	
});	