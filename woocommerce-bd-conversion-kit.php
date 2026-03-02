<?php
/**
 * Plugin Name: WooCommerce BD Conversion Kit
 * Description: Optimizes WooCommerce for the BD market: Bangla localization, Size Charts, Minimal Checkout, and Custom Size Inventory.
 * Version: 1.5
 * Author: Shawon 
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Load Assets Properly
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'wbd-styles', plugin_dir_url( __FILE__ ) . 'assets/css/wc-extra-styles.css' );
    
    if ( is_product() ) {
        wp_enqueue_style( 'google-font-hind', 'https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600&display=swap' );
        wp_enqueue_script( 'wbd-size-modal', plugin_dir_url( __FILE__ ) . 'assets/js/size-chart.js', array(), '1.0', true );
    }

    if ( is_shop() || is_product_category() || is_front_page() ) {
        $product_ids = wc_get_products(['post_type' => 'product', 'return' => 'ids', 'type' => 'simple', 'limit' => -1]);
        $data = [];
        foreach ($product_ids as $id) { $data[] = ['id' => $id, 'url' => get_permalink($id)]; }
        
        wp_enqueue_script( 'wbd-shop-js', plugin_dir_url( __FILE__ ) . 'assets/js/shop-redirect.js', array('jquery'), '1.0', true );
        wp_localize_script( 'wbd-shop-js', 'sizedProductData', $data );
    }
});

/** 2. CUSTOM SIZE INVENTORY SYSTEM **/

add_action('add_meta_boxes', function () {
    add_meta_box('custom_product_sizes', 'Product Sizes (Inventory)', 'wbd_render_size_metabox', 'product', 'side');
});

function wbd_render_size_metabox($post) {
    $sizes = ['39', '40', '41', '42', '43', '44', '45'];
    $saved = get_post_meta($post->ID, '_custom_size_stock', true) ?: [];
    echo '<table style="width:100%;">';
    foreach ($sizes as $size) {
        $val = isset($saved[$size]) ? esc_attr($saved[$size]) : '';
        echo "<tr><td>Size $size:</td><td><input type='number' name='custom_size_stock[$size]' value='$val' style='width:60px;'></td></tr>";
    }
    echo '</table>';
}

add_action('save_post_product', function ($post_id) {
    if (isset($_POST['custom_size_stock'])) update_post_meta($post_id, '_custom_size_stock', $_POST['custom_size_stock']);
});

add_action('woocommerce_before_add_to_cart_button', function () {
    global $product;
    if (!$product->is_type('simple')) return;
    
    $sizes = ['39', '40', '41', '42', '43', '44', '45'];
    $stocks = get_post_meta($product->get_id(), '_custom_size_stock', true) ?: [];

    echo '<div class="custom-size-wrapper"><label>SIZE:</label><select name="custom_size" id="selected_size" required><option value="">Select Size</option>';
    foreach ($sizes as $size) {
        $qty = (isset($stocks[$size]) && $stocks[$size] !== '') ? intval($stocks[$size]) : -1;
        $disabled = ($qty === 0) ? 'disabled' : ''; 
        echo "<option value='$size' $disabled>$size" . ($disabled ? " (Out of stock)" : "") . "</option>";
    }
    echo '</select></div>';
});

add_filter('woocommerce_add_to_cart_validation', function ($passed, $product_id) {
    if (empty($_POST['custom_size']) && wc_get_product($product_id)->is_type('simple')) {
        wc_add_notice('Please select a size.', 'error');
        return false;
    }
    return $passed;
}, 10, 2);

add_filter('woocommerce_add_cart_item_data', function ($cart_data) {
    if (isset($_POST['custom_size'])) $cart_data['custom_size'] = sanitize_text_field($_POST['custom_size']);
    return $cart_data;
});

add_filter('woocommerce_get_item_data', function ($data, $cart_item) {
    if (isset($cart_item['custom_size'])) $data[] = ['name' => 'Size', 'value' => $cart_item['custom_size']];
    return $data;
}, 10, 2);

add_action('woocommerce_checkout_create_order_line_item', function($item, $cart_item_key, $values) {
    if (isset($values['custom_size'])) $item->add_meta_data('Size', $values['custom_size']);
}, 10, 3);

add_action('woocommerce_reduce_order_stock', function($order) {
    foreach ($order->get_items() as $item) {
        $size = $item->get_meta('Size');
        if (!$size) continue;
        $stocks = get_post_meta($item->get_product_id(), '_custom_size_stock', true);
        if (isset($stocks[$size]) && $stocks[$size] !== '') {
            $stocks[$size] = max(0, intval($stocks[$size]) - $item->get_quantity());
            update_post_meta($item->get_product_id(), '_custom_size_stock', $stocks);
        }
    }
});

/** 3. BANLA LOCALIZATION & SIZE CHART **/

add_filter( 'woocommerce_product_tabs', function( $tabs ) {
    $tabs['bangla_description'] = array(
        'title'    => 'বিস্তারিত', 
        'priority' => 15,
        'callback' => 'wbd_bangla_tab_content'
    );
    return $tabs;
});

function wbd_bangla_tab_content() {
    global $product;
    $content = get_post_meta( $product->get_id(), '_bangla_description', true );
    if ( $content ) echo '<div class="bangla-content">' . wpautop( wp_kses_post( $content ) ) . '</div>';
}

add_action( 'woocommerce_single_product_summary', function() {
    global $product;
    $title = get_post_meta( $product->get_id(), '_size_chart_title', true ) ?: 'Size Chart';
    $image = get_post_meta( $product->get_id(), '_size_chart_image', true );
    if ( $image ) {
        echo '<button id="openSizeChart" class="size-chart-btn">' . esc_html( $title ) . '</button>';
        echo '<div id="sizeChartModal" class="modal"><div class="modal-content"><span class="close">&times;</span><img src="'.esc_url($image).'"></div></div>';
    }
}, 11 );

/** 4. MINIMAL CHECKOUT **/

add_filter( 'woocommerce_checkout_fields' , function( $fields ) {
    $remove = ['billing_last_name', 'billing_company', 'billing_address_2', 'billing_city', 'billing_postcode', 'billing_state', 'order_comments'];
    foreach ( $remove as $f ) unset( $fields['billing'][$f] );
    $fields['billing']['billing_first_name']['label'] = 'আপনার নাম';
    $fields['billing']['billing_phone']['label'] = 'ফোন নাম্বার';
    return $fields;
});

add_filter( 'default_checkout_billing_country', function() { return 'BD'; } );
add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false');

// Admin Fields for Bangla & Size Chart
add_action( 'woocommerce_product_options_general_product_data', function() {
    echo '<div class="options_group">';
    woocommerce_wp_textarea_input( ['id' => '_bangla_description', 'label' => 'Bangla Description'] );
    woocommerce_wp_text_input( ['id' => '_size_chart_title', 'label' => 'Size Chart Button Text'] );
    woocommerce_wp_text_input( ['id' => '_size_chart_image', 'label' => 'Size Chart Image URL'] );
    echo '</div>';
});

add_action( 'woocommerce_process_product_meta', function( $post_id ) {
    update_post_meta( $post_id, '_bangla_description', wp_kses_post( $_POST['_bangla_description'] ) );
    update_post_meta( $post_id, '_size_chart_title', sanitize_text_field( $_POST['_size_chart_title'] ) );
    update_post_meta( $post_id, '_size_chart_image', esc_url_raw( $_POST['_size_chart_image'] ) );
});