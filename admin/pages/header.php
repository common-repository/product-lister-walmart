<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
global $cedumbhelper;
$current_page = 'umb-main';

if(isset($_GET['page'])){
	$current_page = sanitize_text_field( $_GET['page'] );
}
?>
<div class="wrap woocommerce ced_umb_pages_wrapper">
	<form novalidate="novalidate" action="" method="post">
		<h2 class="nav-tab-wrapper woo-nav-tab-wrapper ced_umb_nav_tab_wrapper">
			<a href	= "<?php get_admin_url()?>admin.php?page=umb-main" class="nav-tab <?php if( $current_page == 'umb-main' ) : ?> nav-tab-active<?php endif; ?>"><?php _e('Configuration','ced-umb');?></a>
			<a href	= "<?php get_admin_url() ?>admin.php?page=umb-cat-map" class="nav-tab <?php if($current_page == 'umb-cat-map' ) : ?>nav-tab-active<?php endif; ?> "><?php _e('Category Mapping','ced-umb');?></a>
			<a href	= "<?php get_admin_url() ?>admin.php?page=umb-profile" class="nav-tab <?php if($current_page == 'umb-profile' ) : ?>nav-tab-active<?php endif; ?> "><?php  _e('Profile','ced-umb');?></a>  
			<a href	= "<?php get_admin_url() ?>admin.php?page=umb-pro-mgmt" class="nav-tab <?php if($current_page == 'umb-pro-mgmt' ) : ?>nav-tab-active<?php endif; ?> "><?php _e('Manage Products','ced-umb');?></a>
			<a href	= "<?php get_admin_url() ?>admin.php?page=umb-prerequisites" class="nav-tab <?php if($current_page == 'umb-prerequisites' ) : ?>nav-tab-active<?php endif; ?> "><?php _e('Prerequisites','ced-umb');?></a>
			<a href	= "<?php get_admin_url() ?>admin.php?page=umb-features" class="nav-tab <?php if($current_page == 'umb-features' ) : ?>nav-tab-active<?php endif; ?> "><?php _e('Features','ced-umb');?></a>
		</h2>
	</form>
</div>
<div id="ced_umb_marketplace_loader" class="loading-style-bg" style="display: none;">
	<img src="<?php echo plugin_dir_url(__dir__);?>/images/BigCircleBall.gif">
</div>
<?php 
if($current_page!="umb-main"){
	$activated_marketplaces = ced_umb_available_marketplace();

	$validation_notice = array();
	foreach($activated_marketplaces as $activeMarketplace){
		$isValidate = get_option('ced_umb_validate_'.$activeMarketplace);
		if(!$isValidate){
			$message = __('Configuration details of '.$activeMarketplace.' either empty or not validated successfully, please validate the configuration otherwise some processes might not work properly.','ced-umb');
			$classes = "notice notice-error";
			$validation_notice[] = array('message'=>$message, 'classes'=>$classes);
		}
	}	
	if(count($validation_notice)){
		global $cedumbhelper;
		$cedumbhelper->umb_print_notices($validation_notice);
		unset($validation_notice);
	}	
}