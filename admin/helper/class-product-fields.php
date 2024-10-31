<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if( !class_exists( 'CED_UMB_product_fields' ) ) :
/**
 * single product related functionality.
 *
 * Manage all single product related functionality required for listing product on walmart.
 *
 * @since      1.0.0
 * @package    Walmart Product Lister
 * @subpackage Walmart Product Lister/admin/helper
 * @author     CedCommerce <plugins@cedcommerce.com>
 */
class CED_UMB_product_fields{
	
	/**
	 * The Instace of CED_UMB_product_fields.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      $_instance   The Instance of CED_UMB_product_fields class.
	 */
	private static $_instance;
	
	/**
	 * CED_UMB_product_fields Instance.
	 *
	 * Ensures only one instance of CED_UMB_product_fields is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return CED_UMB_product_fields instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Adding tab on product edit page.
	 * 
	 * @since 1.0.0
	 * @param array   $tabs   single product page tabs.
	 * @return array  $tabs
	 */
	public function umb_required_fields_tab( $tabs ){
		
		$tabs['umb_required_fields'] = array(
			'label'  => __( 'WPL', 'ced-umb' ),
			'target' => 'ced_umb_fields',
			'class'  => array( 'show_if_simple','ced_umb_required_fields' ),
			);
		
		return $tabs;
	}
	
	/**
	 * Fields on UMB Required Fields product edit page tab.
	 * 
	 * @since 1.0.0
	 */
	public function umb_required_fields_panel() {
		
		global $post;
		
		if ( $terms = wp_get_object_terms( $post->ID, 'product_type' ) ) {
			$product_type = sanitize_title( current( $terms )->name );
		} else {
			$product_type = apply_filters( 'default_product_type', 'simple' );
		}
		
		if($product_type == 'simple' ){
			require_once CED_UMB_DIRPATH.'admin/partials/umb_product_fields.php';
		}
	}

	/* For Variable Product */
	function umb_render_product_fields_html_for_variations( $loop, $variation_data, $variation ) {
		include CED_UMB_DIRPATH.'admin/partials/umb_product_fields.php';
	}

	function umb_render_variation_html($field_array,$loop,$variation) {
		$requiredInAnyCase = array('_umb_id_type','_umb_id_val','_umb_brand','_umb_walmart_fulfillmentLagTime','_umb_walmart_category');
		$type = esc_attr($field_array['type']);
		if( $type == '_text_input' ) {
			$previousValue = get_post_meta ( $variation->ID, $field_array['fields']['id'], true );
			
			if(in_array($field_array['fields']['id'], $requiredInAnyCase)) {
				$nameToRender = ucfirst($field_array['fields']['label']);
				$nameToRender .= '<span class="ced_umb_wal_required"> [ '.__('Required','ced-umb').' ]</span>';
				$field_array['fields']['label'] = $nameToRender;
			}
			
			?>
			<p class="form-field _umb_brand_field ">
				<label for="<?php echo $field_array['fields']['id']; ?>"><?php echo $field_array['fields']['label']; ?></label>
				<input class="short" style="" name="<?php echo $field_array['fields']['id']; ?>[<?php echo $loop; ?>]" id="<?php echo $field_array['fields']['id']; ?>" value="<?php echo $previousValue; ?>" placeholder="" <?php if($type == 'number') {echo 'type="number"';}else{echo 'type="text"';} ?>> 
				<?php 
				if($field_array['fields']['desc_tip'] == '1') {
					$description = $field_array['fields']['description'];
					echo wc_help_tip( __( $description, 'woocommerce' ) );
				} 
				?>
			</p>
			<?php
		}
		else if( $type == '_select' ) {
			$previousValue = get_post_meta ( $variation->ID, $field_array['fields']['id'], true );
			
			if(in_array($field_array['fields']['id'], $requiredInAnyCase)) {
				$nameToRender = ucfirst($field_array['fields']['label']);
				$nameToRender .= '<span class="ced_umb_wal_required"> ['.__('Required','ced-umb').'  ]</span>';
				$field_array['fields']['label'] = $nameToRender;
			}

			?>
			<p class="form-field _umb_id_type_field ">
				<label for="<?php echo $field_array['fields']['id']; ?>"><?php echo $field_array['fields']['label']; ?></label>
				<select id="<?php echo $field_array['fields']['id']; ?>" name="<?php echo $field_array['fields']['id']; ?>[<?php echo $loop; ?>]" class="select short" style="">
					<?php
					foreach ($field_array['fields']['options'] as $key => $value) {
						if($previousValue == $key) {
							echo '<option value="'.$key.'" selected="selected">'.$value.'</option>';
						}
						else {
							echo '<option value="'.$key.'">'.$value.'</option>';
						}
					}
					?>
				</select> 
				<?php 
				if($field_array['fields']['desc_tip'] == '1') {
					$description = $field_array['fields']['description'];
					echo wc_help_tip( __( $description, 'woocommerce' ) );
				} 
				?>
			</p>
			<?php
		}	
		else if( $type == '_checkbox' ) {
			$previousValue = get_post_meta ( $variation->ID, $field_array['fields']['id'], true );

			if(in_array($field_array['fields']['id'], $requiredInAnyCase)) {
				$nameToRender = ucfirst($field_array['fields']['label']);
				$nameToRender .= '<span class="ced_umb_wal_required"> ['.__('Required','ced-umb').' ]</span>';
				$field_array['fields']['label'] = $nameToRender;
			}

			?>
			<p class="form-field _umb_custom_price_field ">

				<label for="<?php echo $field_array['fields']['id']; ?>"><?php echo $field_array['fields']['label']; ?></label>
				<input class="checkbox" style="" name="<?php echo $field_array['fields']['id']; ?>[<?php echo $loop; ?>]" id="<?php echo $field_array['fields']['id']; ?>" value="yes" placeholder="" type="checkbox" <?php if($previousValue == "yes"){echo 'checked';}  ?> /> 
				<?php 
				if($field_array['fields']['desc_tip'] == '1') {
					$description = $field_array['fields']['description'];
					echo wc_help_tip( __( $description, 'woocommerce' ) );
				} 
				?>
			</p>
			<?php
		}	
		else if ($type == 'lwh') {
			?>
			<p class="form-field dimensions_field">
				<label for="<?php echo $field_array['fields']['id']; ?>"><?php echo $field_array['fields']['label']; ?></label>
				<span class="wrap">
					<input placeholder="<?php esc_attr_e( 'Length', 'ced-umb' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="<?php echo $field_array['fields']['id']; ?>[<?php echo $loop; ?>]" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $variation->ID, $id.'_length', true ) ) ); ?>" />
					<input placeholder="<?php esc_attr_e( 'Width', 'ced-umb' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="<?php echo $field_array['fields']['id']; ?>[<?php echo $loop; ?>]" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $variation->ID, $id.'_width', true ) ) ); ?>" />
					<input placeholder="<?php esc_attr_e( 'Height', 'ced-umb' ); ?>" class="input-text wc_input_decimal last" size="6" type="text" name="<?php echo $field_array['fields']['id']; ?>[<?php echo $loop; ?>]" value="<?php echo esc_attr( wc_format_localized_decimal( get_post_meta( $variation->ID, $id.'_height', true ) ) ); ?>" />
				</span>
				<?php 
				if($field_array['fields']['desc_tip'] == '1') {
					$description = $field_array['fields']['description'];
					echo wc_help_tip( __( $description, 'woocommerce' ) );
				} 
				?>
			</p>
			<?php
		}					
	}
	
	/**
	 * processing product meta required fields for listing
	 * product on walmart.
	 * 
	 * @since 1.0.0
	 * @var int  $post_id
	 */
	public function umb_required_fields_process_meta( $post_id ){
		
		if($_POST['product-type'] == 'variable') {
			
		}
		else {
			$required_fields_ids = $this->get_custom_fields('required',true);
			$extra_fields_ids = $this->get_custom_fields('extra',true);
			$framework_fields = array();
			$framework_fields_ids = array();
			
			$framework_fields = $this->get_custom_fields('framework_specific',false);
			if(count($framework_fields)){
				foreach($framework_fields as $fields_data){
					if(is_array($fields_data)){
						foreach($fields_data as $fields_array){
							if(isset($fields_array['id']))
								$framework_fields_ids[] = esc_attr($fields_array['id']);
						}
					}
				}
			}
			$all_fields = array();
			$all_fields = array_merge($required_fields_ids,$extra_fields_ids,$framework_fields_ids );
			
			foreach($all_fields as $field_name){
				if(isset($_POST[$field_name]))
					update_post_meta( $post_id, $field_name, sanitize_text_field( $_POST[$field_name] ) );
				else 
					update_post_meta( $post_id, $field_name, false);
			}

			do_action( 'ced_umb_required_fields_process_meta_simple', $post_id );
		}
	}

	/**
	 * get product custom fields for preparing
	 * product data information to send on walmart
	 * 
	 * @since 1.0.0
	 * @param string  $type  required|framework_specific|common
	 * @param bool	  $ids  true|false
	 * @return array  fields array
	 */
	public static function get_custom_fields( $type, $is_fields=false ){
		global $post;
		$fields = array();
		
		if($type=='required'){
			
			$required_fields = array(
				array(
					'type' => '_select',
					'id' => '_umb_id_type',
					'fields' => array(
						'id' => '_umb_id_type',
						'label' => __( 'Identifier Type', 'ced-umb' ),
						'options' => array(
							'null' => __('--select--','ced-umb'),
							'ASIN' => __( 'ASIN', 'ced-umb' ),
							'UPC' => __( 'UPC', 'ced-umb' ),
							'EAN' => __( 'EAN', 'ced-umb' ),
							'ISBN-10' => __( 'ISBN-10', 'ced-umb' ),
							'ISBN-13' => __( 'ISBN-13', 'ced-umb' ),
							'GTIN-14' => __( 'GTIN-14', 'ced-umb' ),
							),
						'desc_tip' => true,
						'description' => __( 'Unique identifier type your product must have to list on walmart.', 'ced-umb' ),
						),
					),
				array(
					'type' => '_text_input',
					'id' => '_umb_id_val',
					'fields' => array(
						'id'      	  => '_umb_id_val',
						'label'       => __( 'Identifier Value', 'ced-umb' ),
						'desc_tip'    => true,
						'description' => __( 'Identifier value, for the selected "Identifier Type" above.', 'ced-umb' ),
						),
					),
				array(
					'type' => '_text_input',
					'id' => '_umb_brand',
					'fields' => array(
						'id'            => '_umb_brand',
						'label'         => __( 'Product Brand', 'ced-umb' ),
						'desc_tip'      => true,
						'description'   => __( 'Product brand for sending on walmart.', 'ced-umb' ),
						),
					),
				array(
					'type' => '_text_input',
					'id' => '_umb_manufacturer',
					'fields' => array(
						'id'                => '_umb_manufacturer',
						'label'             => __( 'Product Manufacturer', 'ced-umb' ),
						'desc_tip'          => true,
						'description'       => __( 'Manufacturer of the product.', 'ced-umb' ),
						),
					),
				array(
					'type' => '_text_input',
					'id' => '_umb_mpr',
					'fields' => array(
						'id'                => '_umb_mpr',
						'label'             => __( 'Manufacturer Part Number', 'ced-umb' ),
						'desc_tip'          => true,
						'description'       => __( 'Manufacturer defined unique identifier for an item. An alphanumeric string, max 20 characters including space.', 'ced-umb' ),
						),
					),
				array(
					'type' => '_text_input',
					'id' => '_umb_packsorsets',
					'fields' => array(
						'id'                => '_umb_packsorsets',
						'label'             => __( 'Packs Or Sets', 'ced-umb' ),
						'desc_tip'          => true,
						'description'       => __( 'Identify the package count of this product.', 'ced-umb' ),
						'type'				=> 'number'
						),
					),
				array(
						'type' => '_text_input',
						'id' => '_umb_product_tax_code',
						'fields' => array(
								'id'                => '_umb_product_tax_code',
								'label'             => __( 'Product Tax Code', 'ced-umb' ),
								'desc_tip'          => true,
								'description'       => __( 'Product Tax Code.', 'ced-umb' ),
						),
					),
				);

$fields = is_array( apply_filters('ced_umb_required_product_fields', $required_fields, $post) ) ? apply_filters('ced_umb_required_product_fields', $required_fields, $post) : array() ;
}
else if($type=='extra'){
	$extra_fields = array(
		
		array(
			'type' => '_text_input',
			'id' => '_umb_coo',
			'fields' => array(
				'id'                => '_umb_coo',
				'label'             => __( 'Country Of Origin', 'ced-umb' ),
				'desc_tip'          => true,
				'description'       => __( 'The country that the item was manufactured in.', 'ced-umb' ),
				),
			),
		array(
			'type' => '_checkbox',
			'id' => '_umb_prop65',
			'fields' => array(
				'id'                => '_umb_prop65',
				'label'             => __( 'Prop 65', 'ced-umb' ),
				'desc_tip'          => true,
				'description'       => __( 'Check this if your product is subject to Proposition 65 rules and regulations Proposition 65 requires merchants to provide California consumers with special warnings for products that contain chemicals known to cause cancer, birth defects, or other reproductive harm, if those products expose consumers to such materials above certain threshold levels..', 'ced-umb' ),
				),
			),
		array(
			'type' => '_select',
			'id' => '_umb_cpsia_cause',
			'fields' => array(
				'id' => '_umb_cpsia_cause',
				'label' => __( 'CPSIA cautionary Statements', 'ced-umb' ),
				'options' => array(
					'0' => __( 'no warning applicable', 'ced-umb' ),
					'1' => __( 'choking hazard small parts', 'ced-umb' ),
					'2' => __( 'choking hazard is a small ball', 'ced-umb' ),
					'3' => __( 'choking hazard is a marble', 'ced-umb' ),
					'4' => __( 'choking hazard contains a small ball', 'ced-umb' ),
					'5' => __( 'choking hazard contains a marble', 'ced-umb' ),
					'6' => __( 'choking hazard balloon', 'ced-umb' ),
					),
				'desc_tip' => true,
				'description' => __( 'Use this field to indicate if a cautionary statement relating to the choking hazards of children'."'".'s toys and games applies to your product. These cautionary statements are defined in Section 24 of the Federal Hazardous Substances Act and Section 105 of the Consumer Product Safety Improvement Act of 2008. They must be displayed on the product packaging and in certain online and catalog advertisements.', 'ced-umb' )),
),
array(
	'type' => '_text_input',
	'id' => '_umb_safety_warning',
	'fields' => array(
		'id'                => '_umb_safety_warning',
		'label'             => __( 'Safety Warning', 'ced-umb' ),
		'desc_tip'          => true,
		'description'       => __( 'If applicable, use to supply any associated warnings for your product.', 'ced-umb' ),
		),
	),
array(
	'type' => '_text_input',
	'id' => '_umb_msrp',
	'fields' => array(
		'id'                => '_umb_msrp',
		'label'             => __( 'Manufacturer\'s Suggested Retail Price', 'ced-umb' ),
		'desc_tip'          => true,
		'description'       => __( 'The manufacturer\'s suggested retail price or list price for the product.', 'ced-umb' ),
		'type'              => 'number',
		),
	),

array(
	'type' => '_text_input',
	'id' => '_umb_bullet_1',
	'fields' => array(
		'id'                => '_umb_bullet_1',
		'label'             => __( 'Bullet 1', 'ced-umb' ),
		'desc_tip'          => true,
		'description'       => __( 'bullet points of this product.', 'ced-umb' ),
		),
	),
array(
	'type' => '_text_input',
	'id' => '_umb_bullet_2',
	'fields' => array(
		'id'                => '_umb_bullet_2',
		'label'             => __( 'Bullet 2', 'ced-umb' ),
		'desc_tip'          => true,
		'description'       => __( 'bullet points of this product.', 'ced-umb' ),
		),
	),

);
			//let us decide the other fields depends on the walmart added in the future.
$fields = is_array( apply_filters('ced_umb_extra_product_fields', $extra_fields, $post) ) ? apply_filters('ced_umb_extra_product_fields', $extra_fields, $post) : array() ;
}
else if($type=='framework_specific'){
	
	$framework_fields = array();
	$fields = is_array( apply_filters('ced_umb_framework_product_fields', $framework_fields, $post) ) ? apply_filters('ced_umb_framework_product_fields', $framework_fields, $post) : array() ;
	return $fields;
}
if($is_fields){
	$fields_array = array();
	if(is_array($fields)){
		
		foreach($fields as $field_data){
			$fieldID = isset($field_data['id']) ? esc_attr($field_data['id']) : null;
			if(!is_null($fieldID))
				$fields_array[] = $fieldID;
		}
		return $fields_array;
	}else{
		return array();
	}
	
}else{
	if(is_array($fields)){
		return $fields;
	}else{
		return array();
	}
}
}

	/**
	 * Custom fields html.
	 * 
	 * @since 1.0.0
	 * @param array
	 */
	public function custom_field_html($fieldsArray){
		if(is_array($fieldsArray)){
			foreach($fieldsArray as $data){
				$type = isset($data['type']) ? esc_attr($data['type']) : '_text_input';
				$fields = isset($data['fields']) ? is_array($data['fields']) ? $data['fields'] : array() : array();
				$label = isset($fields['label']) ? esc_attr($fields['label']) : '';
				$description = isset($fields['description']) ? esc_attr($fields['description']) : '';
				$desc_tip = isset($fields['desc_tip']) ? intval($fields['desc_tip']) : !empty($description) ? 1 : 0;
				$fieldvalue = isset($fields['value']) ? $fields['value'] : null;
				echo '<div class="ced_umb_profile_field">';
				echo '<label class="ced_umb_label">';
				echo '<span>'.$label.'</span>';
				echo '</label>';
				switch($type){
					case '_select':
					$id = isset($fields['id']) ? esc_attr($fields['id']) : isset($data['id']) ? esc_attr($data['id']) : null;
					if(!is_null($id)){
						$select_values = isset($fields['options']) ? is_array($fields['options']) ? $fields['options'] : array() : array();
						
						echo '<select name="'.$id.'" id="'.$id.'">';
						if(is_array($select_values)){
							foreach($select_values as $key=>$value){
								echo '<option value="'.$key.'"'.selected($fieldvalue,$key,false).'>';
								echo $value;
								echo '</option>';
							}
						}
						echo '</select>';
					}
					break;
					case '_text_input':
					$id = isset($fields['id']) ? esc_attr($fields['id']) : isset($data['id']) ? esc_attr($data['id']) : null;
					if(!is_null($id)){
						echo '<input type="text" id="'.$id.'" name="'.$id.'" value="'.$fieldvalue.'">';
					}
					break;
					case '_checkbox':
					$id = isset($fields['id']) ? esc_attr($fields['id']) : isset($data['id']) ? esc_attr($data['id']) : null;
					if(!is_null($id)){
						echo '<input type="checkbox" id="'.$id.'" name="'.$id.'" '.checked($fieldvalue,'on').'>';
					}
					break;
					case '_umb_select':
					$id = isset($fields['id']) ? esc_attr($fields['id']) : isset($data['id']) ? esc_attr($data['id']) : null;
					$options = isset($fields['options']) ? $fields['options'] : array();
					$optionsHtml = '';
					$optionsHtml .= '<option value="null">--'.__('select','ced-umb').'--</option>';
					if(is_array($options)){
						foreach($options as $industry => $subcats){
							
							if(is_array($subcats)){
								$optionsHtml .= '<option value="null" class="umb_parent" disabled>'.$industry.'</option>';
								foreach($subcats as $subcatid => $name){
									
									$optionsHtml .= '<option value="'.$subcatid.'" '.selected($fieldvalue,$subcatid,false).'>'.$name.'</option>';
								}
							}
						}
					}
					echo '<p class="form-field '.$id.'">';
					echo '<select name="'.$id.'" id="'.$id.'">';
					echo $optionsHtml;
					echo '</select>';
					echo '</p>';
					break;
				}
				echo '</div>';
			}
		}
	}

	
	
	/**
	 * updated product html after quick edit 
	 * for listing on manage products page of UMB.
	 * 
	 * @since 1.0.0
	 */
	public function response_updated_product_html($post, $product){
		
		if(!class_exists('CED_UMB_product_lister')){
			require_once CED_UMB_DIRPATH.'admin/helper/class-ced-umb-product-listing.php';
			$product_lister = new CED_UMB_product_lister();
			
			if($post->post_type == 'product_variation') {
				$variation_id = $post->ID;
				$post = get_post($post->post_parent);
				return $product_lister->get_product_row_html_variation($post,$variation_id);
			}
			else {
				return $product_lister->get_product_row_html($post);
			}
		}
		return $post->ID;
	}
}

endif;
