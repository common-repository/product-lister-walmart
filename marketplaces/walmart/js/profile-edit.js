jQuery(document).ready(function(){
	renderMarketplaceAttributesSectionHTML( jQuery('select[name^=_umb_walmart_category]'), jQuery('select[name^=_umb_walmart_category]').val(), jQuery('input#profileID').val() );		
	jQuery(document.body).on( 'change', 'select[name^=_umb_walmart_category]', function() {
		renderMarketplaceAttributesSectionHTML( jQuery(this), jQuery(this).val(), jQuery('input#profileID').val() );
	});
	//function to show changes in walmart attributes selection in profile section
	function renderMarketplaceAttributesSectionHTML( thisRef, categoryID, profileID ) {
		jQuery("#ced_umb_marketplace_loader").show();
		jQuery.ajax({
			url : ced_umb_walmart_edit_profile_AJAX.ajax_url,
			type : 'post',
			data : {
				action : 'fetch_walmart_attribute_for_selected_category_for_profile_section',
				categoryID : categoryID,
				profileID : profileID
			},
			success : function( response ) 
			{
				var parentRef = jQuery(thisRef).parents( 'div.ced_umb_toggle_section_wrapper' );
				jQuery(parentRef).siblings('div.ced_umb_walmart_attribute_section').find('div.ced_umb_tabbed_section_wrapper').html(response);
				jQuery("#ced_umb_marketplace_loader").hide();
				jQuery( document.body ).trigger( 'init_tooltips' );
			}
		});
	}
});	