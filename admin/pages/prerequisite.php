<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$section = 'prerequesites'; 
$pre_active = "";
$step_active = "";;
if(isset($_GET['section']))
{
	$section = sanitize_text_field( $_GET['section'] );
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
		<ul class="subsubsub">
			<li><a class="<?php echo $pre_active;?>" href="<?php echo admin_url();?>/admin.php?page=umb-prerequisites&amp;section=prerequesites"><?php _e('PREREQUISITES','ced-umb'); ?></a></li>
			<li>|<a class="<?php echo $step_active;?>" href="<?php echo admin_url();?>/admin.php?page=umb-prerequisites&amp;section=steptofollow"><?php _e('STEPS TO FOLLOW','ced-umb'); ?></a></li>
		</ul>
		
		<?php 
		if($section == 'prerequesites')
		{	
			?>
			<h2 class="ced_umb_setting_header" style="text-align:center"><?php _e('Prerequisites','ced_umb')?></h2>
			<div class = "ced_umb_prerequisite_table_wrap wrap">				
				<table class="wp-list-table widefat fixed striped">					
					<?php
					if (version_compare(PHP_VERSION, '5.5.5') == -1) {
						$php = "not_compatible";
					}
					else {
						$php = "compatible";
					}
					if(!extension_loaded('curl')) {
						$curl = "not_compatible";
					}
					else {
						$curl = "compatible";
					}
					$marketPlaces = ced_umb_available_marketplace();
					$credentials = "Valid";
					$preRequisites = array( "php version 5.5"=>$php , "credentials"=>$credentials,); 
					foreach ($preRequisites as $key=>$preRequisite) {
						?>
						<tr>
							<?php
							if($key == "credentials")
							{
								?>
								<td colspan="4"><?php _e(strtoupper($key),'ced_umb')?></td>
								<td>
									<?php 
									foreach ($marketPlaces as $marketPlace)
									{ 
										$validation = get_option("ced_umb_validate_$marketPlace");
										$validation = (isset($validation) && $validation == "yes") ? "Valid" : "Invalid";
										?>
										<p>
											<?php
											if($validation == "Valid"){
												?>
												<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
												<?php }
												else{?>
												<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
												<?php }?>
											</p>
											<?php 
										}?>
									</td>
									<?php 
								}								
								else{?>
								<td colspan="4"><?php echo strtoupper($key)?></td>
								<td><?php
									if($preRequisite == "compatible"){
										?>
										<img src = "<?php echo CED_UMB_URL; ?>/admin/images/check.png">
										<?php }
										else{?>
										<img src = "<?php echo CED_UMB_URL; ?>/admin/images/cross.png">
										<?php }?>
									</td>
									<?php 
								}?>
							</tr>
							<?php 
						}
						?>
					</table>					
					<br/>
					<h2 class="ced_umb_setting_header ced_umb_bottom_margin" style="text-align:center"><?php _e('Guidelines','ced_umb');?></h2>					
					<table class="wp-list-table widefat fixed striped">
						<tr>
							<th colspan="4"><b><?php _e('Parameters','ced_umb')?></b></th>
							<th><b><?php _e('Status','ced_umb')?></b></th>
						</tr>
						<?php
						$required = array(	
							'Product Identifier' => __('A valid product identifier with valid product identifier code.','ced-umb'),
							'Product Taxcode' => __('A valid product Tax code.','ced-umb'),
							'Product Description' => __('Product description should be availble for the products to be uploaded.','ced-umb')
							);
							foreach ($required as $k=>$v){?>
							<tr>
								<td colspan = '4'><?php echo strtoupper($k);?></td>
								<td><?php echo $v;?></td>
							</tr>
							<?php }
							?>
						</table>
					</div>
					<?php 
				}
				if($section == 'steptofollow')
				{
					$marketPlaces = ced_umb_available_marketplace();
					if(isset($marketPlaces) && !empty($marketPlaces))
					{
						?>
						<div class="ced_umb_steptofollow">
							<h2 class="ced_umb_setting_header" style="text-align:center"><?php _e('Steps to follow','ced_umb')?></h2>
							
							<table class="wp-list-table widefat fixed striped ced_umb_steptofollow">
								<tr>
									<th>
										<b><?php _e('STEPS','ced_umb')?></b>
									</th>
									<?php 
									foreach($marketPlaces as $marketPlace)
									{
										?>
										<td>
											<b><?php echo strtoupper($marketPlace);?></b>
										</td>
										<?php 
									}	
									?>
								</tr>
								<tr>
									<th>
										<b><?php _e('Configuration Save','ced_umb')?></b>
									</th>
									<?php 
									foreach($marketPlaces as $marketPlace)
									{
										?>
										<td>
											<?php echo strtoupper(get_option("ced_umb_save_".$marketPlace,"no"));?>
										</td>
										<?php 
									}	
									?>
								</tr>
								<tr>
									<th>
										<b><?php _e('Configuration Validation','ced_umb')?></b>
									</th>
									<?php 
									foreach($marketPlaces as $marketPlace)
									{
										?>
										<td>
											<?php echo strtoupper(get_option('ced_umb_validate_'.$marketPlace,"no"));?>
										</td>
										<?php 
									}	
									?>
								</tr>
								<tr>
									<th>
										<b><?php _e('Category Mapping','ced_umb')?></b>
									</th>
									<?php 
									foreach($marketPlaces as $marketPlace)
									{
										?>
										<td>
											<?php $catmap = get_option('ced_umb_selected_'.$marketPlace.'_categories',false);
											if(isset($catmap) && !empty($catmap))
											{
												_e('YES','ced_umb');
											}	
											else
											{
												_e('NO','ced_umb');
											}	
											?>
										</td>
										<?php 
									}	
									?>
								</tr>
								<tr>
									<th>
										<b><?php _e('Product Upload','ced_umb')?></b>
									</th>
									<?php 
									foreach($marketPlaces as $marketPlace)
									{
										?>
										<td>
											<?php
											$upload= get_option("ced_umb_".$marketPlace."_uploadfeed", false);
											if(isset($upload) && !empty($upload))
											{
												_e('YES','ced_umb');
											}	
											else
											{
												_e('NO','ced_umb');
											}	
											?>
										</td>
										<?php 
									}	
									?>
								</tr>
							</table>
						</div>
						<?php
					}	
				}
				?>
			</div>
		</div>