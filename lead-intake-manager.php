<?php
/**
 * Plugin Name: WP Lead Intake Manager
 * Description: A small lead intake form plugin with shortcode output and an admin lead list.
 * Version: 1.0.0
 * Author: Interview Project
 * Text Domain: lead-intake-manager
 *
 * @package LeadIntakeManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LIM_VERSION', '1.0.0' );
define( 'LIM_PLUGIN_FILE', __FILE__ );
define( 'LIM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LIM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once LIM_PLUGIN_DIR . 'includes/class-lim-db.php';
require_once LIM_PLUGIN_DIR . 'includes/class-lim-activator.php';
require_once LIM_PLUGIN_DIR . 'includes/class-lim-form-handler.php';
require_once LIM_PLUGIN_DIR . 'includes/class-lim-shortcode.php';
require_once LIM_PLUGIN_DIR . 'includes/class-lim-admin.php';

register_activation_hook( __FILE__, array( 'LIM_Activator', 'activate' ) );

/**
 * Boot the plugin after WordPress is loaded.
 */
function lim_bootstrap() {
	$form_handler = new LIM_Form_Handler();
	$form_handler->register_hooks();

	$shortcode = new LIM_Shortcode();
	$shortcode->register_hooks();

	if ( is_admin() ) {
		$admin = new LIM_Admin();
		$admin->register_hooks();
	}
}
add_action( 'plugins_loaded', 'lim_bootstrap' );
