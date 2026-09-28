<?php
/**
 * Plugin Name: WooCommerce BD Conversion Kit
 * Description: Bangladesh-focused WooCommerce sizing, Bangla content, size charts and checkout customisation.
 * Version: 1.6.0
 * Author: Shajjadur Rahaman Shawon
 * Text Domain: woocommerce-bd-conversion-kit
 */
if ( ! defined( 'ABSPATH' ) ) exit;
define( 'WBD_VERSION', '1.6.0' );
function wbd_sizes(){ return array('39','40','41','42','43','44','45'); }
function wbd_stock($id){ $v=get_post_meta($id,'_custom_size_stock',true); return is_array($v)?$v:array(); }

add_action('wp_enqueue_scripts',function(){
 if(!function_exists('is_woocommerce')) return;
 if(is_product()){
  wp_enqueue_style('wbd-styles',plugin_dir_url(__FILE__).'assets/css/wc-extra-styles.css',array(),WBD_VERSION);
  wp_enqueue_script('wbd-size-modal',plugin_dir_url(__FILE__).'assets/js/size-chart.js',array(),WBD_VERSION,true);
 }
});
add_action('add_meta_boxes',function(){ add_meta_box('custom_product_sizes',__('Product Sizes (Inventory)','woocommerce-bd-conversion-kit'),'wbd_render_size_metabox','product','side'); });
function wbd_render_size_metabox($post){
 wp_nonce_field('wbd_save_sizes','wbd_sizes_nonce'); $saved=wbd_stock($post->ID);
 echo '<table style="width:100%">';
 foreach(wbd_sizes() as $size){ $val=isset($saved[$size])?absint($saved[$size]):''; printf('<tr><td>%s</td><td><input type="number" min="0" step="1" name="custom_size_stock[%s]" value="%s" style="width:70px"></td></tr>',esc_html(sprintf(__('Size %s:','woocommerce-bd-conversion-kit'),$size)),esc_attr($size),esc_attr($val)); }
 echo '</table>';
}
add_action('save_post_product',function($post_id){
 if(!isset($_POST['wbd_sizes_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wbd_sizes_nonce'])),'wbd_save_sizes')) return;
 if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE) return;
 if(!current_user_can('edit_post',$post_id)) return;
 $raw=isset($_POST['custom_size_stock'])?(array)wp_unslash($_POST['custom_size_stock']):array(); $clean=array();
 foreach(wbd_sizes() as $size){ if(isset($raw[$size])&&$raw[$size]!=='') $clean[$size]=absint($raw[$size]); }
 update_post_meta($post_id,'_custom_size_stock',$clean);
});
add_action('woocommerce_before_add_to_cart_button',function(){
 global $product; if(!$product||!$product->is_type('simple')) return; $stocks=wbd_stock($product->get_id()); if(!$stocks) return;
 echo '<div class="custom-size-wrapper"><label for="selected_size">'.esc_html__('Size','woocommerce-bd-conversion-kit').'</label><select name="custom_size" id="selected_size" required><option value="">'.esc_html__('Select Size','woocommerce-bd-conversion-kit').'</option>';
 foreach(wbd_sizes() as $size){ if(!array_key_exists($size,$stocks)) continue; $qty=absint($stocks[$size]); printf('<option value="%s"%s>%s%s</option>',esc_attr($size),disabled(0,$qty,false),esc_html($size),0===$qty?' '.esc_html__('(Out of stock)','woocommerce-bd-conversion-kit'):''); }
 echo '</select></div>';
});
add_filter('woocommerce_product_add_to_cart_url',function($url,$product){ return ($product&&$product->is_type('simple')&&wbd_stock($product->get_id()))?$product->get_permalink():$url; },10,2);
add_filter('woocommerce_product_add_to_cart_text',function($text,$product){ return ($product&&$product->is_type('simple')&&wbd_stock($product->get_id()))?__('Select Size','woocommerce-bd-conversion-kit'):$text; },10,2);
add_filter('woocommerce_loop_add_to_cart_args',function($args,$product){ if($product&&$product->is_type('simple')&&wbd_stock($product->get_id())&&isset($args['class']))$args['class']=trim(str_replace('ajax_add_to_cart','',$args['class'])); return $args; },10,2);
add_filter('woocommerce_add_to_cart_validation',function($passed,$product_id,$quantity){
 $product=wc_get_product($product_id); if(!$product||!$product->is_type('simple')) return $passed; $stocks=wbd_stock($product_id); if(!$stocks) return $passed;
 $size=isset($_POST['custom_size'])?sanitize_text_field(wp_unslash($_POST['custom_size'])):'';
 if(!in_array($size,wbd_sizes(),true)||!array_key_exists($size,$stocks)){ wc_add_notice(__('Please select a valid size.','woocommerce-bd-conversion-kit'),'error'); return false; }
 $requested=absint($quantity); if(WC()->cart){ foreach(WC()->cart->get_cart() as $item){ if(absint($item['product_id'])===absint($product_id)&&isset($item['custom_size'])&&$item['custom_size']===$size)$requested+=absint($item['quantity']); } } if(absint($stocks[$size])<$requested){ wc_add_notice(__('The selected size does not have enough stock.','woocommerce-bd-conversion-kit'),'error'); return false; }
 return $passed;
},10,3);
add_filter('woocommerce_add_cart_item_data',function($data,$product_id){
 if(isset($_POST['custom_size'])){ $size=sanitize_text_field(wp_unslash($_POST['custom_size'])); if(in_array($size,wbd_sizes(),true)) $data['custom_size']=$size; } return $data;
},10,2);
add_filter('woocommerce_get_item_data',function($data,$item){ if(!empty($item['custom_size'])) $data[]=array('name'=>__('Size','woocommerce-bd-conversion-kit'),'value'=>esc_html($item['custom_size'])); return $data; },10,2);
add_action('woocommerce_checkout_create_order_line_item',function($item,$key,$values){ if(!empty($values['custom_size'])) $item->add_meta_data(__('Size','woocommerce-bd-conversion-kit'),$values['custom_size']); },10,3);
add_action('woocommerce_reduce_order_stock',function($order){
 if(!$order instanceof WC_Order||$order->get_meta('_wbd_size_stock_reduced')) return;
 foreach($order->get_items() as $item){ $size=$item->get_meta('Size'); if(!$size) continue; $id=$item->get_product_id(); $stocks=wbd_stock($id); if(isset($stocks[$size])){ $stocks[$size]=max(0,absint($stocks[$size])-absint($item->get_quantity())); update_post_meta($id,'_custom_size_stock',$stocks); } }
 $order->update_meta_data('_wbd_size_stock_reduced',1); $order->save();
});
add_filter('woocommerce_product_tabs',function($tabs){ $tabs['bangla_description']=array('title'=>__('বিস্তারিত','woocommerce-bd-conversion-kit'),'priority'=>15,'callback'=>'wbd_bangla_tab_content'); return $tabs; });
function wbd_bangla_tab_content(){ global $product; $c=$product?get_post_meta($product->get_id(),'_bangla_description',true):''; if($c) echo '<div class="bangla-content">'.wpautop(wp_kses_post($c)).'</div>'; }
add_action('woocommerce_single_product_summary',function(){
 global $product; if(!$product)return; $title=get_post_meta($product->get_id(),'_size_chart_title',true)?:__('Size Chart','woocommerce-bd-conversion-kit'); $image=get_post_meta($product->get_id(),'_size_chart_image',true);
 if($image){ echo '<button type="button" id="openSizeChart" class="size-chart-btn" aria-haspopup="dialog">'.esc_html($title).'</button><div id="sizeChartModal" class="modal" role="dialog" aria-modal="true" aria-label="'.esc_attr($title).'" hidden><div class="modal-content"><button type="button" class="close" aria-label="'.esc_attr__('Close size chart','woocommerce-bd-conversion-kit').'">&times;</button><img src="'.esc_url($image).'" alt="'.esc_attr($title).'"></div></div>'; }
},11);
add_filter('woocommerce_checkout_fields',function($fields){ foreach(array('billing_last_name','billing_company','billing_address_2','billing_city','billing_postcode','billing_state') as $f) unset($fields['billing'][$f]); if(isset($fields['billing']['billing_first_name']))$fields['billing']['billing_first_name']['label']='আপনার নাম'; if(isset($fields['billing']['billing_phone']))$fields['billing']['billing_phone']['label']='ফোন নাম্বার'; return $fields; });
add_filter('default_checkout_billing_country',fn()=>'BD');
add_action('woocommerce_product_options_general_product_data',function(){ echo '<div class="options_group">'; woocommerce_wp_textarea_input(array('id'=>'_bangla_description','label'=>__('Bangla Description','woocommerce-bd-conversion-kit'))); woocommerce_wp_text_input(array('id'=>'_size_chart_title','label'=>__('Size Chart Button Text','woocommerce-bd-conversion-kit'))); woocommerce_wp_text_input(array('id'=>'_size_chart_image','label'=>__('Size Chart Image URL','woocommerce-bd-conversion-kit'))); echo '</div>'; });
add_action('woocommerce_process_product_meta',function($id){ if(!current_user_can('edit_post',$id))return; if(isset($_POST['_bangla_description']))update_post_meta($id,'_bangla_description',wp_kses_post(wp_unslash($_POST['_bangla_description']))); if(isset($_POST['_size_chart_title']))update_post_meta($id,'_size_chart_title',sanitize_text_field(wp_unslash($_POST['_size_chart_title']))); if(isset($_POST['_size_chart_image']))update_post_meta($id,'_size_chart_image',esc_url_raw(wp_unslash($_POST['_size_chart_image']))); });
