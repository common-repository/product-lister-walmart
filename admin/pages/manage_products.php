<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
//product listing class.
require_once CED_UMB_DIRPATH.'admin/helper/class-ced-umb-product-listing.php';

require_once CED_UMB_DIRPATH.'admin/helper/class-feed-manager.php';

//header file.
require_once CED_UMB_DIRPATH.'admin/pages/header.php';

$notices = array();
if(isset($_POST['doaction'])){
	check_admin_referer('bulk-ced_umb_mps');
	
	$action = isset($_POST['action']) ? sanitize_text_field( $_POST['action'] ): -1;
	$marketPlaces = get_enabled_marketplaces();
	$marketPlace = is_array($marketPlaces) && !empty($marketPlaces) ? $marketPlaces[0] : -1;
	$marketplace = isset($_REQUEST['section']) ? $_REQUEST['section'] : $marketPlace;
	$proIds = isset($_POST['post']) ? $_POST['post'] : array();
	$allset = true;
	
	if(empty($action) || $action== -1){
		$allset = false;
		$message = __('Please select the  actions to perform action!','ced-umb');
		$classes = "error is-dismissable";
		$notices[] = array('message'=>$message, 'classes'=>$classes);
	}
	
	if(empty($marketplace) || $marketplace== -1){
		$allset = false;
		$message = __(' walmart is not activated!','ced-umb');
		$classes = "error is-dismissable";
		$notices[] = array('message'=>$message, 'classes'=>$classes);
	}
	
	if(!is_array($proIds)){
		$allset = false;
		$message = __('Please select products to perform  action!','ced-umb');
		$classes = "error is-dismissable";
		$notices[] = array('message'=>$message, 'classes'=>$classes);
	}
	 
	if($allset){
		
		if( class_exists( 'CED_UMB_feed_manager' ) ){
			$feed_manager = CED_UMB_feed_manager::get_instance();
			$notice = $feed_manager->process_feed_request($action,$marketplace,$proIds);
			$notice_array = json_decode($notice,true);
			if(is_array($notice_array)){
				
				$message = isset($notice_array['message']) ? $notice_array['message'] : '' ;
				$classes = isset($notice_array['classes']) ? $notice_array['classes'] : 'error is-dismissable';
				$notices[] = array('message'=>$message, 'classes'=>$classes);
			}else{
				
				$message = __('Unexpected error encountered, please try again!','ced-umb');
				$classes = "error is-dismissable";
				$notices[] = array('message'=>$message, 'classes'=>$classes);
			}
		}
	}
}

if(count($notices))
{
	foreach($notices as $notice_array)
	{
		$message = isset($notice_array['message']) ? esc_html($notice_array['message']) : '';
		$classes = isset($notice_array['classes']) ? esc_attr($notice_array['classes']) : 'error is-dismissable';
		if(!empty($message))
		{?>
			 <div class="<?php echo $classes;?>">
			 	<p><?php echo $message;?></p>
			 </div>
		<?php 	
		}
	}
	unset($notices);
}

$availableMarketPlaces = get_enabled_marketplaces();
if(is_array($availableMarketPlaces) && !empty($availableMarketPlaces)) {
	$section = $availableMarketPlaces[0];
	if(isset($_GET['section'])) {
		$section = esc_attr($_GET['section']);
	}
	$product_lister = new CED_UMB_product_lister();
	$product_lister->prepare_items();
	?>
	<div class="ced_umb_wrap">
		<?php do_action("ced_umb_manage_product_before_start");?>
		
		<h2 class="ced_umb_setting_header"><?php _e('Manage Products','ced-umb'); ?></h2>
		
		<?php do_action("ced_umb_manage_product_after_start");?>
		
		<form method="get" action="">
			<input type="hidden" name="page" value="<?php echo sanitize_text_field($_REQUEST['page']) ?>" />
			<?php $product_lister->search_box('Search Products', 'search_id');?>
		</form>
		 

		<form method="get" action="">
			<input type="hidden" name="page" value="<?php echo sanitize_text_field( $_REQUEST['page']) ?>" />
			<?php
			/** Sorting By Status  **/
			$status_actions = array(
				'published'    => __( 'Published', 'ced-umb' ),
				'notUploaded'    => __( 'Not Uploaded', 'ced-umb' ),
			);
			$previous_selected_status = isset($_GET['status_sorting']) ? sanitize_text_field ( $_GET['status_sorting'] ) : '';
		 	
			
			$product_categories = get_terms( 'product_cat', array('hide_empty'=>false) );
		 	$temp_array = array();
		 	foreach ($product_categories as $key => $value) {
		 		$temp_array[$value->term_id] = $value->name;
		 	}
		 	$product_categories = $temp_array;
		 	$previous_selected_cat = isset($_GET['pro_cat_sorting']) ? sanitize_text_field ( $_GET['pro_cat_sorting'] ): '';
		 	

		 	$product_types = get_terms( 'product_type', array('hide_empty'=>false) );
		 	$temp_array = array();
		 	foreach ($product_types as $key => $value) {
		 		if( $value->name == 'simple' || $value->name == 'variable' ) {
		 			$temp_array[$value->term_id] = ucfirst($value->name);
		 		}
		 	}
		 	$product_types = $temp_array;
		 	$previous_selected_type = isset($_GET['pro_type_sorting']) ? sanitize_text_field( $_GET['pro_type_sorting'] ): '';
		 	
			echo '<div class="ced_umb_top_wrapper">';
				echo '<select name="status_sorting">';
				echo '<option value="">' . __( 'Product Status', 'ced-umb' ) . "</option>";
				foreach ( $status_actions as $name => $title ) {
					$selectedStatus = ($previous_selected_status == $name) ? 'selected="selected"' : '';
					$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';
					echo '<option '.$selectedStatus.' value="' . $name . '"' . $class . '>' . $title . "</option>";
				}
				echo "</select>";

				echo '<select name="pro_cat_sorting">';
				echo '<option value="">' . __( 'Product Category', 'ced-umb' ) . "</option>";
				foreach ( $product_categories as $name => $title ) {
					$selectedCat = ($previous_selected_cat == $name) ? 'selected="selected"' : '';
					$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';
					echo '<option '.$selectedCat.' value="' . $name . '"' . $class . '>' . $title . "</option>";
				}
				echo "</select>";				 
				submit_button( __( 'Filter', 'ced-umb' ), 'action', '', false, array() );
			echo '</div>';
			?>
		</form>
		<form id="ced_umb_products" method="post">
		<?php $product_lister->views(); ?> 	
		<?php ?>			
		<?php $product_lister->display() ?>
		</form>
		<?php if($product_lister->has_items()):?>
			<?php  $product_lister->inline_edit(); ?>
			<?php  $product_lister->profle_section(); ?>
		<?php endif;?>
	</div>
	<?php
}
else{
?><h3><?php	_e('Please validate walmart configuration first.','ced-umb');?></h3><?php
}