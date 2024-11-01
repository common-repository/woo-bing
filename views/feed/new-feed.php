<?php

$header_path = dirname(__FILE__) . '/header.php';

if ( file_exists( $header_path ) ) {
    require_once $header_path;
}
$taxonomies = get_option( 'woobing_google_product_type' );
$taxonomies = $taxonomies ? $taxonomies : array();

$offset   = 20;
$products = woobing_get_products( 20 );

while( count( $products ) ) {
    $new_some_products = woobing_get_products( 20, $offset );
    $products          = array_merge( $products, $new_some_products );
    
    if ( ! count( $new_some_products ) ) {
        break;
    }
    
    $offset            = $offset + 20;
}

$product_log = array();

foreach ( $products as $key => $product) {
    $product_log[$product->ID] = $product->post_title;
}


$feed_fields = array();
$feed_id     = isset( $_GET['feed_id'] ) ? intval( $_GET['feed_id'] ) : 0;

if ( $feed_id ) {
	$post = get_post( $feed_id );
}

$feed_fields['id'] = array(
	'type'     => 'hidden',
	'value'    => $feed_id
);

$feed_fields['post_title'] = array(
	'type'     => 'text',
	'class'    => 'woobing-field',
	'value'    => isset( $post->post_title ) ? $post->post_title : '',
	'label'    => __( 'Name', 'woobing' ),

	'extra' => array(
        'data-woobing_validation'         => true,
        'data-woobing_required'           => true,
        'data-woobing_required_error_msg' => __( 'This field is required', 'woobing' ),
        'required' => 'required'
    )
);

$feed_fields['seller_name'] = array(
	'type'     => 'text',
	'class'    => 'woobing-field',
	'value'    => get_post_meta( $feed_id, '_seller_name', true ),
	'label'    => __( 'Seller Name', 'woobing' ),
	'extra' => array(
        'data-woobing_validation'         => true,
        'data-woobing_required'           => true,
        'data-woobing_required_error_msg' => __( 'This field is required', 'woobing' ),
        'required' => 'required'
    )
);

$feed_fields['all_products'] = array(
	'label' => __( 'Products', 'woobing' ),
	'type'  => 'checkbox',
	'desc'  => __( 'Enable all products feed', 'woobing' ),
    'fields' => array(
        array(
			'label'   => __( 'All', 'woobing' ),
			'value'   => 'on',
			'checked' => get_post_meta( $feed_id, '_all_products', true ),
			'class'   => 'woobing-all-product-checkbox-feed woobing-field',
        ),
    ),
);

$selected_products = get_post_meta( $feed_id, '_products', true );

$feed_fields['products[]'] = array(
	'type'       => 'multiple',
	'label'      => __( 'Some Individual Products', 'woobing' ),
	'option'     => isset( $product_log ) ? $product_log : array(),
	'class'      => 'woobing-chosen',
	'selected'   => empty( $selected_products ) ? array() : $selected_products,
	'desc'       => 'Chose products',
	'wrap_start' => true,
	'wrap_attr' => array(
		'class' => 'woobing-product-chosen-field-wrap'
	),
	'wrap_close' => true,
);

$feed_fields['variable_products'] = array(
	'label' => __( 'With Variable product', 'woobing' ),
	'type'  => 'checkbox',
	'desc'  => __( 'Include variable product', 'woobing' ),
    'fields' => array(
        array(
			'label'   => __( 'Variable product', 'woobing' ),
			'value'   => 'yes',
			'checked' => get_post_meta( $feed_id, '_woobing_include_variable_products', true ),
			'class'   => 'woobing-field',
        ),
    ),
);

$feed_fields['brand'] = array(
	'type'     => 'text',
	'class'    => 'woobing-field',
	'value'    => get_post_meta( $feed_id, '_brand', true ),
	'label'    => __( 'Brand', 'woobing' ),
	'desc'     => __( 'Brand of the item', 'woobing' )
);

$feed_fields['mpn'] = array(
	'type'     => 'checkbox',
	'label'    => __( 'MPN (SKU)', 'woobing' ),
	'desc'     => __( 'Manufacturer Part Number (MPN) of the item', 'woobing' ),
	'fields' => array(
        array(
            'label' => __( 'Enable', 'woobing' ),
            'value' => 'on',
            'checked' => get_post_meta( $feed_id, '_mpn', true ),
            'class'    => 'woobing-field',
        ),
    ),
);

$feed_fields['gender'] = array(
	'type'     => 'select',
	'class'    => 'woobing-field',
	'label'    => __( 'Gender', 'woobing' ),
	'selected' => get_post_meta( $feed_id, '_gender', true ),
	'option'   => array( '-1' => '--Select--', 'male' => 'male', 'female' => 'female', 'unisex' => 'unisex' ),
	'desc'     => __( 'Gender of the item', 'woobing' )
);

$feed_fields['age_group'] = array(
	'type'     => 'select',
	'class'    => 'woobing-field',
	'selected' => get_post_meta( $feed_id, '_age_group', true ),
	'label'    => __( 'Age Group', 'woobing' ),
	'option'   => array( '-1' => '--Select--', 'newborn' => 'newborn', 'infant' => 'infant', 'toddler' => 'toddler', 'kids' => 'kids', 'adult' => 'adult' ),
	'desc'     => __( 'Availability status of the item', 'woobing' )
);

$feed_fields['color'] = array(
	'type'     => 'checkbox',
	'label'    => __( 'Color', 'woobing' ),
	'desc'     => __( 'Color of the item. This color get from individual product color attributes value. Remember color attribute spell must be color', 'woobing' ),
	'fields' => array(
        array(
            'label' => __( 'Enable', 'woobing' ),
            'value' => 'on',
            'checked' => get_post_meta( $feed_id, '_color', true ),
            'class'    => 'woobing-field',
        ),
    ),
);

$feed_fields['size'] = array(
	'type'     => 'checkbox',
	'label'    => __( 'Size', 'woobing' ),
	'desc'     => __( 'Size of the item. This size get from individual product size attributes value. Remember size attribute spell must be size', 'woobing' ),
	'fields' => array(
        array(
            'label' => __( 'Enable', 'woobing' ),
            'value' => 'on',
            'checked' => get_post_meta( $feed_id, '_size', true ),
            'class'    => 'woobing-field',
        ),
    ),
);

$feed_fields['availability'] = array(
	'type'     => 'select',
	'class'    => 'woobing-field',
	'selected' => get_post_meta( $feed_id, '_availability', true ),
	'label'    => __( 'Availability', 'woobing' ),
	'option'   => array( '-1' => __( '-select-', 'woobing' ), 'in stock' => 'in stock', 'out of stock' => 'out of stock', 'preorder' => 'preorder' ),
	'desc'     => __( 'Availability status of the item', 'woobing' )
);

$feed_fields['google_product_category'] = array(
	'type'     => 'select',
	'label'    => __( 'Category', 'woobing' ),
	'option'   => isset( $taxonomies ) ? $taxonomies : array(),
	'class'    => 'woobing-chosen',
	'selected' => get_post_meta( $feed_id, '_google_product_category', true ),
	'desc'     => __( 'Google\'s category of the item', 'woobing' )
);

$feed_fields['condition'] = array(
	'type'     => 'select',
	'class'    => 'woobing-field',
	'selected' => get_post_meta( $feed_id, '_condition', true ),
	'label'    => __( 'Condition', 'woobing' ),
	'option'   => array( '-1' => __( '-select-', 'woobing' ), 'new' => 'New', 'used' => 'Used', 'refurbished' => 'Refurbished' ),
	'desc'     => __( 'Availability status of the item', 'woobing' )
);

$feed_fields['expiration_date'] = array(
	'type'     => 'text',
	'class'    => 'woobing-field',
	'class'    => 'woobing-date-picker',
	'value'    => get_post_meta( $feed_id, '_expiration_date', true ),
 	'label'    => __( 'Expiration Date', 'woobing' ),
	'desc'     => __( 'Date that an item will expire', 'woobing' ),
);

$feed_fields['product_type'] = array(
	'type'     => 'text',
	'label'    => __( 'Product Type', 'woobing' ),
	//'option'   => isset( $taxonomies ) ? $taxonomies : array(),
	'class'    => 'woobing-field',
	'value'    => get_post_meta( $feed_id, '_product_type', true ),
	'desc'     => __( 'e.g. Home > Electronics > DVD Player', 'woobing' )
);

$feed_fields['sale_price'] = array(
	'label' => __( 'Sale Price', 'woobing' ),
	'type'  => 'checkbox',
	'desc'  => __( 'Include sale price', 'woobing' ),
    'fields' => array(
        array(
			'label'   => __( 'Sale Price', 'woobing' ),
			'value'   => 'yes',
			'checked' => get_post_meta( $feed_id, '_sale_price', true ),
			'class'   => 'woobing-field',
        ),
    ),
);

$feed_fields['sale_price_effective_date'] = array(
	'label' => __( 'Effective Date', 'woobing' ),
	'type'  => 'checkbox',
	'desc'  => __( 'Sale price effective date', 'woobing' ),
    'fields' => array(
        array(
			'label'   => __( 'Date', 'woobing' ),
			'value'   => 'yes',
			'checked' => get_post_meta( $feed_id, '_sale_price_effective_date', true ),
			'class'   => 'woobing-field',
        ),
    ),
);

$feed_fields['custom_label_0'] = array(
	'type'     => 'text',
	'label'    => __( 'Custom Label 0', 'woobing' ),
	'class'    => 'woobing-field',
	'value'    => get_post_meta( $feed_id, '_custom_label_0', true ),
	//'desc'     => __( 'Brand of the item', 'woobing' )
);

$feed_fields['custom_label_1'] = array(
	'type'     => 'text',
	'label'    => __( 'Custom Label 1', 'woobing' ),
	'class'    => 'woobing-field',
	'value'    => get_post_meta( $feed_id, '_custom_label_1', true ),
	//'desc'     => __( 'Brand of the item', 'woobing' )
);

$feed_fields['custom_label_2'] = array(
	'type'     => 'text',
	'label'    => __( 'Custom Label 2', 'woobing' ),
	'class'    => 'woobing-field',
	'value'    => get_post_meta( $feed_id, '_custom_label_2', true ),
	//'desc'     => __( 'Brand of the item', 'woobing' )
);

$feed_fields['custom_label_3'] = array(
	'type'     => 'text',
	'label'    => __( 'Custom Label 3', 'woobing' ),
	'class'    => 'woobing-field',
	'value'    => get_post_meta( $feed_id, '_custom_label_3', true ),
	//'desc'     => __( 'Brand of the item', 'woobing' )
);

$feed_fields['custom_label_4'] = array(
	'type'     => 'text',
	'label'    => __( 'Custom Label 4', 'woobing' ),
	'class'    => 'woobing-field',
	'value'    => get_post_meta( $feed_id, '_custom_label_4', true ),
	//'desc'     => __( 'Brand of the item', 'woobing' )
);

?>

<div class="postbox">

	<h3 class="postbox-title"><?php _e( 'Generate Products Feed', 'woobing' ); ?></h3>
	
	<div class="woobing-feed-form-content">
		<form method="post" class="woobing-feed-form" action="">
		<?php
			wp_nonce_field( 'woobing_feed_nonce', 'feed_nonce' );
			foreach( $feed_fields as $name => $field_obj ) {

			    if( ! isset( $field_obj['type'] ) || empty( $field_obj['type'] ) ) {
			        continue;
			    }

			    switch ( $field_obj['type'] ) {
			        case 'text':
			            echo WooBing_Admin_Settings::getInstance()->text_field( $name, $field_obj );
			            break;
			        case 'select':
			            echo WooBing_Admin_Settings::getInstance()->select_field( $name, $field_obj );
			            break;
			        case 'textarea':
			            echo WooBing_Admin_Settings::getInstance()->textarea_field( $name, $field_obj );
			            break;
			        case 'radio':
			            echo WooBing_Admin_Settings::getInstance()->radio_field( $name, $field_obj );
			            break;
			        case 'checkbox':
			            echo WooBing_Admin_Settings::getInstance()->checkbox_field( $name, $field_obj );
			            break;
			        case 'hidden':
			            echo WooBing_Admin_Settings::getInstance()->hidden_field( $name, $field_obj );
			            break;
			        case 'multiple':
			            echo WooBing_Admin_Settings::getInstance()->multiple_select_field( $name, $field_obj );
			            break;
			        case 'html':
			            echo WooBing_Admin_Settings::getInstance()->html_field( $name, $field_obj );
			            break;
			    }
			}
			?>
			<div class="woobing-clear"></div>
			<input type="submit" class="button button-primary" value="<?php _e( 'Create Feed', 'woobing'); ?>" name="woobing_submit_feed">
		</form>
	</div>
	<div class="woobing-clear"></div>
</div>


