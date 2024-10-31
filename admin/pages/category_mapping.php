<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
 //header file.
require_once CED_UMB_DIRPATH.'admin/pages/header.php';
//to enable walmart
$activated_marketplaces	 = get_enabled_marketplaces();
?>
<div id="ced_umb_marketplace_loader" class="loading-style-bg" style="display: none;">
	<img src="<?php echo plugin_dir_url(__dir__);?>/images/BigCircleBall.gif">
</div>
<?php 
//if walmart is activate then list with enabled label
if(is_array($activated_marketplaces) && !empty($activated_marketplaces)){
	$count = 1;
	echo '<div class="ced_umb_wrap">';
	foreach($activated_marketplaces as $marketplace){
		
		$file_path = CED_UMB_DIRPATH.'marketplaces/'.$marketplace.'/partials/ced-umb-cat-mapping.php';
		if(file_exists($file_path)){
			require_once $file_path;
		}else{
			if($count == count($marketplace) && !$mappingRequired){
				_e('This process is not required for currently walmart.','ced-umb');
			}
		}
		$count++;
	}
	echo '</div>';
}else{
?><h3><?php	_e('Please validate walmart configuration first.','ced-umb'); ?></h3><?php
} 