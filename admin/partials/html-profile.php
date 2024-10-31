<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
?> 
<div class="ced_umb_overlay">
	<div class = "ced_umb_hidden_profile_section ced_umb_wrap">
		<p class="ced_umb_button_right">
			<span class="ced_umb_overlay_cross ced_umb_white_txt">X</span>
			<p>
				<h2 class="ced_umb_setting_header"><?php _e("Select profile for this product","ced_umb");?></h2>
				<label class="ced_umb_white_txt"><?php _e('Available Profile','ced_umb');?></label>
				<?php 
				global $wpdb;
				$table_name = $wpdb->prefix.CED_UMB_PREFIX.'profiles';
				$query = "SELECT `id`, `name` FROM `$table_name` WHERE `active` = 1";
				$profiles = $wpdb->get_results($query,'ARRAY_A');
				if(count($profiles)){?>
				<select class="ced_umb_profile_select">
					<option value="0"> --<?php _e('select','ced-umb');?>-- </option>
					<?php 
					foreach($profiles as $profileInfo){
						$profileId = isset($profileInfo['id']) ? intval($profileInfo['id']) : 0;
						$profileName = isset($profileInfo['name']) ? $profileInfo['name'] : '';
						if($profileId){
							?>
							<option value = "<?php echo $profileId; ?>"><?php echo $profileName; ?></option>
							<?php 
						}
					}
					?>
				</select>
				<button type = "button" data-prodid = "" class="ced_umb_save_profile button button-ced_umb"><?php _e("Save profile")?></button>
				<?php 
			}else{
				?>
				<p class="ced_umb_white_txt"><?php _e('No any profile available to assign, please create a profile or enable and came back to assing!','ced-umb');?></p>
				<?php }?>
			</div>
		</div>