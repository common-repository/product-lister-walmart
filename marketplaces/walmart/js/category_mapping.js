jQuery(document).ready(function(){

	jQuery(document.body).on( 'click', 'input:checkbox[class=ced_umb_walmart_cat_select]', function() {
		if(jQuery(this).is(':checked')) {
			updateWalmartCategoriesInDB( jQuery(this).val(), jQuery(this).attr('name'), 'append' );
		}
		else {
			updateWalmartCategoriesInDB( jQuery(this).val(), jQuery(this).attr('name'), 'delete' );
		}
	});
  	
	/**
	 * updating walmart categories
	 * 
	 */
	function updateWalmartCategoriesInDB( categoryNAME, categoryID , actionToDo ) {
		jQuery("#ced_umb_marketplace_loader").show();
		jQuery.ajax({
			url : umb_walmart_cat_map.ajax_url,
			type : 'post',
			data : {
				action : 'updateWalmartCategoriesInDB',
				categoryID : categoryID,
				categoryNAME : categoryNAME,
				actionToDo : actionToDo
			},
			success : function( response ) 
			{
				jQuery("#ced_umb_marketplace_loader").hide();
			}
		});
	}
});	