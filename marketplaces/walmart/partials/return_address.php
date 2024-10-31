<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

//header file.
require_once CED_UMB_DIRPATH.'admin/pages/header.php';

/* saving and getting values */
if(isset($_POST['saveAddress'])) {
	global $cedumbhelper;
	$message = __("Your address is Saved Successfully",'ced-umb');
	$classes = "notice notice-success";
	$validation_notice[] = array('message'=>$message, 'classes'=>$classes);
	$cedumbhelper->umb_print_notices($validation_notice);
	unset($validation_notice);

	$ced_umb_return_country = isset($_POST['ced_umb_return_country'])? sanitize_text_field( $_POST['ced_umb_return_country']):'';
	$ced_umb_return_state = isset($_POST['ced_umb_return_state'])?sanitize_text_field($_POST['ced_umb_return_state']):'';
	$ced_umb_return_first_address = isset($_POST['ced_umb_return_first_address'])?sanitize_text_field($_POST['ced_umb_return_first_address']):'';
	$ced_umb_return_second_address = isset($_POST['ced_umb_return_second_address'])?sanitize_text_field($_POST['ced_umb_return_second_address']):'';
	$ced_umb_return_city = isset($_POST['ced_umb_return_city'])?sanitize_text_field($_POST['ced_umb_return_city']):'';
	$ced_umb_return_zip_code = isset($_POST['ced_umb_return_zip_code'])?sanitize_text_field($_POST['ced_umb_return_zip_code']):'';

	$returnAddress = array(
		'ced_umb_return_country' => $ced_umb_return_country,
		'ced_umb_return_state' => $ced_umb_return_state,
		'ced_umb_return_first_address' => $ced_umb_return_first_address,
		'ced_umb_return_second_address' => $ced_umb_return_second_address,
		'ced_umb_return_city' => $ced_umb_return_city,
		'ced_umb_return_zip_code' => $ced_umb_return_zip_code
	);

	update_option('ced_umb_merchant_return_address', $returnAddress);

}
$returnAddress = get_option('ced_umb_merchant_return_address', false);
$ced_umb_return_country = isset($returnAddress['ced_umb_return_country'])?$returnAddress['ced_umb_return_country']:'';
$ced_umb_return_state = isset($returnAddress['ced_umb_return_state'])?$returnAddress['ced_umb_return_state']:'';
$ced_umb_return_first_address = isset($returnAddress['ced_umb_return_first_address'])?$returnAddress['ced_umb_return_first_address']:'';
$ced_umb_return_second_address = isset($returnAddress['ced_umb_return_second_address'])?$returnAddress['ced_umb_return_second_address']:'';
$ced_umb_return_city = isset($returnAddress['ced_umb_return_city'])?$returnAddress['ced_umb_return_city']:'';
$ced_umb_return_zip_code = isset($returnAddress['ced_umb_return_zip_code'])?$returnAddress['ced_umb_return_zip_code']:'';
?>
<br/>
<div class="ced_umb_bottom_margin">
	<h2 class="ced_umb_setting_header"><?php _e('Return Address','ced-umb');?></h2>
	<span class="ced_umb_white_txt"><?php _e('This address will be used in case of return.','ced-umb');?></span>
</div>
<div class="ced_umb_return_address">
	<form method="post">
		<table class="ced_umb_return_address wp-list-table widefat fixed striped activityfeeds ced_umb_config_table" >
			<tbody>
				<tr>
					<th><?php _e('Country','ced-umb');?></th>
					<td class="manage-column"><input required type="text" name="ced_umb_return_country" value="<?php echo $ced_umb_return_country; ?>"></td>
				</tr>
				<tr>
					<th><?php _e('State','ced-umb');?></th>
					<td class="manage-column"><input required type="text" name="ced_umb_return_state" value="<?php echo $ced_umb_return_state; ?>"></td>
				</tr>
				<tr>
					<th><?php _e('First Address','ced-umb');?></th>
					<td class="manage-column"><textarea required name="ced_umb_return_first_address"><?php echo $ced_umb_return_first_address; ?></textarea></td>
				</tr>
				<tr>
					<th><?php _e('Second Address','ced-umb');?></th>
					<td class="manage-column"><textarea name="ced_umb_return_second_address"><?php echo $ced_umb_return_second_address; ?></textarea></td>
				</tr>
				<tr>
					<th><?php _e('City','ced-umb');?></th>
					<td class="manage-column"><input required type="text" name="ced_umb_return_city" value="<?php echo $ced_umb_return_city; ?>"></td>
				</tr>
				<tr>
					<th><?php _e('Zip Code','ced-umb');?></th>
					<td class="manage-column"><input required type="text" name="ced_umb_return_zip_code" value="<?php echo $ced_umb_return_zip_code; ?>"></td>
				</tr>
			</tbody>
		</table>
		<p class="ced_umb_button_right">
			<input class="button button-ced_umb" value="<?php _e('Save Address','ced-umb');?>" name="saveAddress" type="submit">
		</p>
	</form>	
</div>