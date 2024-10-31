<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
//this page is used to display all features for comparing between walmart product lister free version with it paid version functionality
$section = 'prerequesites'; 
$pre_active = "";
$step_active = "";;
if(isset($_GET['section']))
{
	$section = sanitize_text_field ( $_GET['section'] );
	if($section == 'prerequesites')
	{
		$pre_active = "current";
	}	
	else
	{
		$step_active = "current";
	}	
}	
else 
{
	$pre_active = "current";
}		
require_once CED_UMB_DIRPATH.'admin/pages/header.php';?>
<div class="ced_umb_prerequisite_wrapper">
	<div class="ced_umb_wrap">
		<a   href="http://cedcommerce.com/woocommerce-extensions/walmart-woocommerce-integration"><h2 class="ced_umb_setting_header" style="text-align:center"><?php _e('Purchase Walmart-WooCommerce Integration Paid Version','ced_umb')?></h2></a>
		<div class = "ced_umb_prerequisite_table_wrap wrap">			
			<table class="wp-list-table widefat fixed striped">
				<tr>
					<th colspan="3"><b><?php _e('Feature','ced_umb')?></b></th>
					<th>
						<b><?php _e('Free Version','ced_umb')?></b>
					</th>
					<th>
						<b><?php _e('Paid Version','ced_umb')?></b>
					</th>
				</tr>
				
				<tr>
					<th colspan="5"></th>
				</tr>
				<tr>
					<td colspan="3"><b><?php _e('Simple Products Uplaod on walmart','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>

				<tr>
					<td colspan="3"><b><?php _e('Variable Products Uplaod on walmart','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>
				<tr>
					<td colspan="3"><b><?php _e('Quick Edit For Walmart Specific field','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>
				<tr>
					<td colspan="3"><b><?php _e('Profile Assignment','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>
				<tr>
					<td colspan="3"><b><?php _e('Auto Acknowledgement','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>
				<tr>
					<td colspan="3"><b><?php _e('Upload CSV file To Upload Products Details On Walmart','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>

				<tr>
					<td colspan="3"><b><?php _e('Auto Price And Inventory Syn On Walmart','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>

				<tr>
					<td colspan="3"><b><?php _e('Fetch Order Details From Walmart','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>

				<tr>
					<td colspan="3"><b><?php _e('Order Status','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>

				<tr>
					<td colspan="3"><b><?php _e('Order Cancellation','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>

				<tr>
					<td colspan="3"><b><?php _e('Refund Option For Cancelled Product','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>

				<tr>
					<td colspan="3"><b><?php _e('Shipping Status','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>
 
				<tr>
					<td colspan="3"><b><?php _e('Add Products To Queque, Archieve & Unarchieve','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>
				<tr>
					<td colspan="3"><b><?php _e('Upload Your Queque Products On Walmart','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>
				<tr>
					<td colspan="3"><b><?php _e('Walmart Configuration','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>

				<tr>
					<td colspan="3"><b><?php _e('Return Address','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>

				<tr>
					<td colspan="3"><b><?php _e('Categories Mapping','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>

				<tr>
					<td colspan="3"><b><?php _e('Look For Categories Update','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>

				<tr>
					<td colspan="3"><b><?php _e('Products Upload By Specific Categories On Walmart','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>

				<tr>
					<td colspan="3"><b><?php _e('Search And Filter Products','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>				

				<tr>
					<td colspan="3"><b><?php _e('Find Feeds Status With Error Report','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>

				<tr>
					<td colspan="3"><b><?php _e('Prerequisites','ced_umb')?></b></td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
					<td>
						<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
					</td>
				</tr>

			</table>
			<br/>
		</div>
		<a   href="http://cedcommerce.com/woocommerce-extensions/walmart-woocommerce-integration"><h2 class="ced_umb_setting_header" style="text-align:center"><?php _e('Purchase Walmart-WooCommerce Integration Paid Version','ced_umb')?></h2></a>
	</div>
</div>