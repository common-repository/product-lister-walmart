//function for manage profile section details
jQuery(document).ready(function(){
	jQuery(document.body).on("click",".ced_umb_profile",function(){
		var prodId = jQuery(this).attr("data-proid");
		jQuery(".ced_umb_save_profile").attr("data-prodid",prodId);
		jQuery(".ced_umb_overlay").show();
	});
	jQuery(document.body).on("click",".ced_umb_overlay_cross",function(){
		jQuery(".ced_umb_overlay").hide();
	})
	//function to remove profile details
	jQuery(document.body).on("click",".umb_remove_profile",function(){

		var proId     = jQuery(this).attr("data-prodid");
		jQuery("#ced_umb_marketplace_loader").show();
		var profileId = 0;
		var data  = {
			"action"    : "ced_umb_save_profile",
			"proId"     : proId,
			"profileId" : profileId
		}
		jQuery.post(
			profile_action_handler.ajax_url,
			data,
			function(response)
			{
				jQuery("#ced_umb_marketplace_loader").hide();

				jQuery(".ced_umb_overlay").hide();
				if(response != "success")
				{
					window.location.reload();
				}
				else
				{
					window.location.reload();
				}	
			}
			)
		.fail(function() {
			jQuery("#ced_umb_marketplace_loader").hide();
			window.location.reload();

		})
	})
	// function to save profile details
	jQuery(document.body).on("click",".ced_umb_save_profile",function(){

		var proId     = jQuery(this).attr("data-prodid");
		jQuery("#ced_umb_marketplace_loader").show();

		var profileId = jQuery(".ced_umb_profile_select option:selected").val();
		var data  = {
			"action"    : "ced_umb_save_profile",
			"proId"     : proId,
			"profileId" : profileId
		}
		jQuery.post(
			profile_action_handler.ajax_url,
			data,
			function(response) {
				jQuery("#ced_umb_marketplace_loader").hide();

				jQuery(".ced_umb_overlay").hide();
				if(response != "success") {
					window.location.reload();
				}
				else {
					window.location.reload();
				}	
			}
			)
		.fail(function() {
			jQuery("#ced_umb_marketplace_loader").hide();
			window.location.reload();
		});
	});
});