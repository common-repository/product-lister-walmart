(function( $ ) {
	'use strict';
	jQuery(document).ready(function(){
		$("input[name=ced_umb_walmart_channel_id]").attr('readonly', true);


		//toggle fields.
		jQuery(document).on('click','#ced_umb_accordian .ced_umb_panel_heading',function(){
			var k = jQuery(this).next().slideToggle('slow');
			jQuery('.ced_umb_collapse').not(k).slideUp('slow');
		});

		// price management fields.
		jQuery("#_umb_custom_price").on('change',function(){
			if(this.checked){
				jQuery(".umb_price_fields").show();
			}else{
				jQuery(".umb_price_fields").hide();
			}
		});
		// stock management fields.
		jQuery("#_umb_custom_stock").on('change',function(){
			if(this.checked){
				jQuery(".umb_stock_fields").show();
			}else{
				jQuery(".umb_stock_fields").hide();

			}
		});

		/* Handle walmart Addition **/
		jQuery('.upload-view-toggle').on('click',function(){
			jQuery('.ced-umb-upload-addon').slideToggle();
		});
	});
})( jQuery );