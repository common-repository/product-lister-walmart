<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$walmartCategoryPath = 'json/WalmartCategories.json';
$walmartCategoryPath = CED_UMB_DIRPATH.'marketplaces/'.$marketplace.'/partials/'.$walmartCategoryPath;
ob_start();
readfile($walmartCategoryPath);
$json_data = ob_get_clean();
$categories = json_decode($json_data, TRUE);

$selectedWalmartCategories = get_option('ced_umb_selected_walmart_categories');
if(isset($selectedWalmartCategories) && !empty($selectedWalmartCategories)) {
	$selectedWalmartCategories = json_decode($selectedWalmartCategories,TRUE);
}

?>
<div class="ced_umb_walmart_cat_mapping ced_umb_toggle_wrapper">
	<div class="ced_umb_toggle_section">
		<div class="ced_umb_toggle">
			<h2><?php _e('CATEGORIES','ced-umb');?></h2>
		</div>
		<div class="ced_umb_cat_activate_ul ced_umb_toggle_div">
			 
		<?php 
		$breakPoint = floor(count($categories)/3);
		$counter = 0;
		
		sksort_temp($categories, "path", true);
		
		foreach ($categories as $key => $category) {
			if( $counter == 0 ) {
				echo '<ul class="ced_walmart_cat_ul">';
			}
			$catName = str_replace("/", " >> ", $category['path']);
			if(is_array($selectedWalmartCategories) && array_key_exists($category['path'],$selectedWalmartCategories)) {
				echo '<li><input type="checkbox" class="ced_umb_walmart_cat_select" name="'.$category['path'].'" value="'.$catName.'" checked >'.$catName."</li>";
			}
			else {
				echo '<li><input type="checkbox" class="ced_umb_walmart_cat_select" name="'.$category['path'].'" value="'.$catName.'">'.$catName."</li>";
			}

			if( $counter == $breakPoint ) {
				$counter = 0;
				echo '</ul>';
			}
			else{
				$counter++;
			}
		}
		?>
		</div>
	</div>
</div>

<?php
/**
* Function for shorting 
*/
function sksort_temp(&$array, $subkey="id", $sort_ascending=false) {

    if (count($array))
        $temp_array[key($array)] = array_shift($array);

    foreach($array as $key => $val){
        $offset = 0;
        $found = false;
        foreach($temp_array as $tmp_key => $tmp_val)
        {
            if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
            {
                $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
                                            array($key => $val),
                                            array_slice($temp_array,$offset)
                                          );
                $found = true;
            }
            $offset++;
        }
        if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
    }
    if ($sort_ascending) $array = array_reverse($temp_array);
    else $array = $temp_array;
}