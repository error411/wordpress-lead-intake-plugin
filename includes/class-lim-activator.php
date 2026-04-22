<?php
/**
 * Plugin activation tasks.
 *
 * @package LeadIntakeManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the custom lead table on activation.
 */
class LIM_Activator {
	/**
	 * Run activation tasks.
	 */
	public static function activate() {
		LIM_DB::create_table();
	}
}
