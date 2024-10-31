<?php
global $cedumbhelper;
/** save configuration setup of marketplace **/	
if( isset( $_POST['marketplaceID'] ) && isset( $_POST['ced_umb_save_marketplace_config'] ) && $_POST['ced_umb_save_marketplace_config']=='Update' ) {
	do_action( 'ced_umb_save_marketplace_configuration_settings' , $_POST, sanitize_text_field( $_POST['marketplaceID'] ) );
}
/** validate configuration setup of marketplace **/	
if( isset( $_POST['ced_umb_marketplace_configuration'] ) && isset($_POST['ced_umb_validate_marketplace_config'])  ) {
	do_action( 'ced_umb_validate_marketplace_configuration_settings' , $_POST, sanitize_text_field( $_POST['marketplaceID'] ) );
}
$configSettings = array();
$configSettingsData = apply_filters( 'ced_umb_render_marketplace_configuration_settings', $configSettings, 'walmart' );
$configSettings = $configSettingsData['configSettings'];
$showUpdateButton = !$configSettingsData['showUpdateButton'];
$marketPlaceName = $configSettingsData['marketPlaceName'];
require_once CED_UMB_DIRPATH.'admin/pages/header.php';
?>
<div class="ced_umb_wrap">
	<h2 class="ced_umb_setting_header ced_umb_bottom_margin"><?php echo $marketPlaceName;?> Configuration</h2>
	<div>
		<form method="post">
			<input type="hidden" name="ced_umb_marketplace_configuration" value="1" >
			<input type="hidden" name="marketplaceID" value="walmart" >

			<table class="wp-list-table widefat fixed striped ced_umb_config_table">
				<tbody>
					<?php
					foreach ($configSettings as $key => $value) {
						echo '<tr>';
						echo '<th class="manage-column">';
						echo $value['name'];
						echo '</th>';
						echo '<td class="manage-column">';
						if($value['type'] == 'text') {
							echo '<input type="text" name="'.$key.'" value="'.$value['value'].'">';
						}
						do_action( 'ced_umb_render_different_input_type' , $value['type']);
						echo '</td>';
						echo '</tr>';
					}
					?>
				</tbody>
				<tfoot>
					<tr>
						<td></td>
						<td>
							<input class="button button-ced_umb" type="submit" name="ced_umb_save_marketplace_config" value="<?php _e('Update','ced-umb');?>">
							<?php if($showUpdateButton) {
								echo '<input class="button button-ced_umb" type="submit" name="ced_umb_validate_marketplace_config" value="'.__('Validate','ced-umb').'">';	
							}
							do_action('ced_umb_render_imp_links');
							?>
						</td>
					</tr>	
				</tfoot>
			<table>
		</form>
		<?php 
		$marketPlaceName = str_replace(" ", "", $marketPlaceName);
		do_action("ced_".$marketPlaceName."_additional_configuration", $marketPlaceName);
		?>
	</div>	
<div>