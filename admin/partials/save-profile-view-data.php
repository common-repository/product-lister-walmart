<?php
if(!session_id()) {
	session_start();
}

global $wpdb;
$table_name = $wpdb->prefix.CED_UMB_PREFIX.'profiles';

if( isset($_POST['add_meta_keys']) || isset($_POST['saveProfile']) ) {
	
	$profileid = isset($_POST['profileID']) ? sanitize_text_field( $_POST['profileID'] ) : false;
	$profileName = isset($_POST['profile_name']) ?sanitize_text_field( $_POST['profile_name'] ) : '';
	if($profileName==''){
		$notice['message'] = __('Please fill profile name first.','ced-umb');
		$notice['classes'] = "notice notice-success";
		$validation_notice[] = $notice;
		$_SESSION['ced_umb_validation_notice'] = $validation_notice;
		return;
		
	}
	$is_active = isset($_POST['enable']) ? '1' : '0';
	$marketplaceName = isset($_POST['marketplaceName']) ? sanitize_text_field ( $_POST['marketplaceName'] ): 'all';
	
	$updateinfo = array();
	
	foreach ($_POST['ced_umb_required_common'] as $key) {
		$arrayToSave = array();
		isset($_POST[$key][0]) ? $arrayToSave['default']=$_POST[$key][0] : $arrayToSave['default']='';
		if($key == '_umb_'.$marketplaceName.'_subcategory') {
			isset($_POST[$key]) ? $arrayToSave['default']=$_POST[$key] : $arrayToSave['default']='';
		}
		isset($_POST[$key.'_attibuteMeta']) ? $arrayToSave['metakey']=$_POST[$key.'_attibuteMeta'] : $arrayToSave['metakey']='null';
		$updateinfo[$key] = $arrayToSave;
	}

	$updateinfo = apply_filters('umb_save_additional_profile_info',$updateinfo);
	$updateinfo['selected_product_id'] = isset($_POST['selected_product_id']) ? sanitize_text_field ( $_POST['selected_product_id'] ): '';
	$updateinfo['selected_product_name'] = isset($_POST['ced_umb_pro_search_box']) ? sanitize_text_field ( $_POST['ced_umb_pro_search_box'] ): '';
	$updateinfo = json_encode($updateinfo);

	if($profileid){
		$wpdb->update($table_name, array('name'=>$profileName,'active'=>$is_active,'marketplace'=>$marketplaceName,'profile_data'=>$updateinfo), array('id'=>$profileid));
		
		$notice['message'] = __('Profile Updated Successfully.','ced-umb');
		$notice['classes'] = "notice notice-success";
		$validation_notice[] = $notice;
		$_SESSION['ced_umb_validation_notice'] = $validation_notice;
		
	}else{
		$wpdb->insert($table_name, array('name'=>$profileName,'active'=>$is_active,'marketplace'=>$marketplaceName,'profile_data'=>$updateinfo));
		global $wpdb;
		$prefix = $wpdb->prefix . CED_UMB_PREFIX;
		$tableName = $prefix.'profiles';
		$sql = "SELECT * FROM `".$tableName."` ORDER BY `id` DESC";
		$queryData = $wpdb->get_results($sql,'ARRAY_A');
		$profileid = $queryData[0]['id'];

		$notice['message'] = __('Profile Created Successfully.','ced-umb');
		$notice['classes'] = "notice notice-success";
		$validation_notice[] = $notice;
		$_SESSION['ced_umb_validation_notice'] = $validation_notice;
		
		$redirectURL = get_admin_url().'admin.php?page=umb-profile&action=edit&message=created&profileID='.$profileid;
		wp_redirect($redirectURL);
		die;
	}
}
