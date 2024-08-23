<?php
/*
Plugin Name: SM WooCommerce Special Products
Plugin URI: https://siteman.top
Description: Filter special products for customers and guest users
Version: 0.0.1
Author: SiteMan
Author URI: https://siteman.top
Text Domain: sm-wooCommerce-special-products
Requires at least: 5.2
Tested up to: 6.6.1
Stable tag: trunk
License: GPL
*/

function has_special_tag( $post_id ) {
    return has_term( 'special', 'product_tag', $post_id );
}

function hide_special_products( $query ) {
    if ( ! is_admin() && $query->is_main_query() && ( is_shop() || is_product_category() || is_product_tag() ) ) {
        if ( ! is_user_logged_in() || current_user_can( 'customer' ) ) {
            $query->set( 'tax_query', array(
                array(
                    'taxonomy' => 'product_tag',
                    'field'    => 'slug',
                    'terms'    => array( 'special' ),
                    'operator' => 'NOT IN',
                ),
            ) );
        }
    }
}
add_filter( 'pre_get_posts', 'hide_special_products' );

function hide_special_related_products( $related_posts ) {
    if ( ! is_admin() && is_single() ) {
        if ( is_user_logged_in() ) {
            return $related_posts;
        } else {
            return array_filter( $related_posts, function( $post_id ) {
                return ! has_special_tag( $post_id );
            } );
        }
    }
}
add_filter( 'woocommerce_related_products', 'hide_special_related_products' );


function redirect_to_my_account_for_special_product() {
    global $post;

    if ( has_special_tag( $post->ID ) && ! is_user_logged_in() ) {
        wp_redirect( wc_get_account_endpoint_url( 'my-account' ) );
        exit;
    }
}
add_action( 'woocommerce_before_single_product', 'redirect_to_my_account_for_special_product' );
