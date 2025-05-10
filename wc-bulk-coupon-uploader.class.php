<?php
    /*
        Plugin Name: WC Bulk Coupon Uploader
        Author: Darell Duma
        Author URI: https://darellduma.com
        Version: 0.1
        Description: Coupon Uploader for WooCommerce Coupons. Upload a CSV files and convert each row into coupons.
    */

    defined( 'ABSPATH' ) or die( 'You are not allowed to access this file' );

    // Check if WooCommerce is active
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');

    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        // Deactivate this plugin
        deactivate_plugins(plugin_basename(__FILE__));

        // Display admin notice
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>WC Bulk Coupon Uploader</strong> requires WooCommerce to be installed and active.</p></div>';
        });

        return;
    }


    if( ! class_exists( 'WCBulkCouponUploader' ) ){
        class WCBulkCouponUploader{

            function admin_enqueue_scripts( $hook ){
                if ( 'marketing_page_bulk-coupon-upload' != $hook ) {
                    return;
                }
                wp_enqueue_style( 'wc-bulk-coupon-uploader-style', plugin_dir_url( __FILE__ ) . 'style.css', [], '1.0' );
                wp_enqueue_script( 'sweetalert2', '//cdn.jsdelivr.net/npm/sweetalert2@11', [], '11', true );
                wp_enqueue_script( 'papaParse', 'https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.1/papaparse.min.js', [ 'jquery' ], '5', true );
                wp_enqueue_script( 'wc-bulk-coupon-uploader-main-script', plugin_dir_url( __FILE__ ) . 'script.js', [ 'jquery' ], '1.0', true );
                wp_add_inline_script( 'wc-bulk-coupon-uploader-main-script', 'const WCBCU = ' . json_encode( [
                    'ajaxURL'   =>    admin_url( 'admin-ajax.php' ),
                    'site_url'  =>    site_url()
                 ] ), 'before' );
            }

            function bulk_coupon_uploader_html(){
                require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/wc-bulk-coupon-uploader/views/uploader.php';
            }

            function process_record(){

                if( get_page_by_title( $_POST[ 'coupon_code' ], OBJECT, 'shop_coupon' ) ){
                    wp_send_json([
                        'success'   =>  false,
                        'message'   =>  'Either invalid data or coupon code exists'
                    ]);
                }

                global $wpdb;

                $coupon_code = sanitize_text_field( $_POST[ 'coupon_code'] );

                $wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->posts SET
                    post_author=%d,
                    post_date=%s,
                    post_date_gmt=%s,
                    post_title=%s,
                    post_excerpt=%s,
                    post_status='publish',
                    comment_status='closed',
                    ping_status='closed',
                    post_name=%s,
                    post_modified=%s,
                    post_modified_gmt=%s,
                    post_type='shop_coupon'
                    ",
                    get_current_user_id(),
                    current_time( 'mysql' ),
                    current_time( 'mysql', 8 ),
                    $coupon_code,
                    sanitize_text_field( $_POST['description'] ),
                    $coupon_code,
                    current_time( 'mysql' ),
                    current_time( 'mysql', 8 )
                ) );
            
                $coupon_id = $wpdb->insert_id;
                
		        $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET guid=%s WHERE ID=%d", esc_url_raw( add_query_arg( array( 'post_type' => 'shop_coupon', 'p' => $coupon_id ), home_url() ) ), $coupon_id ) ); // 10% faster -1 query per coupon

                $product_ids = isset( $_POST[ 'product_ids' ] ) ? sanitize_text_field( $_POST[ 'product_ids' ] ) : array();
                
                update_post_meta( $coupon_id, 'coupon_amount', sanitize_text_field( $_POST[ 'coupon_amount' ] ) );
                update_post_meta( $coupon_id, 'expiry_date', strtotime( sanitize_text_field( ($_POST[ 'expiry_date' ] ) ) ) );
                update_post_meta( $coupon_id, 'discount_type', sanitize_text_field( $_POST[ 'discount_type' ] ) );
                update_post_meta( $coupon_id, 'product_ids', $product_ids );
                update_post_meta( $coupon_id, 'usage_limit', sanitize_text_field($_POST[ 'usage_limit' ]) );

                $wpdb->query( 'COMMIT' );

                wp_send_json( [ 'success' => true, 'couponId' => $coupon_id ] );
            }

            function options_page(){
                global $admin_page_hooks;
                $parent_menu = ( isset( $admin_page_hooks['woocommerce-marketing'] ) ) ? 'woocommerce-marketing' : 'woocommerce';
                add_submenu_page( 
                    $parent_menu, 
                    'Bulk Coupon Upload',
                    'Bulk Coupon Upload',
                    'manage_options',
                    'bulk-coupon-upload',
                    [ $this, 'bulk_coupon_uploader_html' ]
                );
            }
        }

        $plugin = new WCBulkCouponUploader();

        //actions
        add_action( 'admin_menu', [ $plugin, 'options_page' ] );
        add_action( 'admin_enqueue_scripts', [ $plugin, 'admin_enqueue_scripts' ] );
        //end actions

        //wp_ajax
        add_action( 'wp_ajax_process_record', [ $plugin, 'process_record' ] );
        //end wp_ajax

        register_activation_hook( __FILE__, function(){ flush_rewrite_rules(); } );
        register_deactivation_hook( __FILE__, function(){ flush_rewrite_rules(); } );


    }