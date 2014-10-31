<?php
/**
 * Plugin Name:       Codeboxr Dashboard Widget Manager
 * Plugin URI:        http://codeboxr.com/product/dashboard-widget-manager-for-wordpress
 * Description:       Manage  your dashboard widgets
 * Version:           1.1.5
 * Author:            Codeboxr
 * Author URI:        http://www.codeboxr.com
 * Text Domain:       cbdashboardmanager
 * License:           GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
// get public class
require_once( plugin_dir_path( __FILE__ ) . 'public/class-cbdashboardmanager.php' );

// get instance of public class
add_action( 'plugins_loaded', array( 'cbdashboardmanager', 'get_instance' ) );
// get admin class
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-cbdashboardmanager-admin.php' );
	add_action( 'plugins_loaded', array( 'cbdashboardmanager_Admin', 'get_instance' ) );

}
